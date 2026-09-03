<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_order_id')->nullable()->unique()->after('gateway');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('plan_id')->constrained('payments')->nullOnDelete();
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->enum('usage_type', ['listing', 'contact']);
            $table->timestamp('used_at');
            $table->timestamps();
            $table->unique(['subscription_id', 'usage_type', 'room_id'], 'subscription_usage_unique');
            $table->index(['user_id', 'usage_type']);
        });

        // Preserve credits already consumed under the legacy null-payment convention.
        DB::table('subscriptions')->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.status', 'active')->select('subscriptions.*', 'plans.type')->get()
            ->each(function ($subscription) {
                $roomIds = $subscription->type === 'owner'
                    ? DB::table('rooms')->where('user_id', $subscription->user_id)->where('listing_fee_paid', true)->whereNull('listing_payment_id')->pluck('id')
                    : DB::table('enquiries')->where('user_id', $subscription->user_id)->where('unlocked', true)->whereNull('payment_id')->pluck('room_id');
                foreach ($roomIds as $roomId) {
                    DB::table('subscription_usages')->insertOrIgnore([
                        'subscription_id' => $subscription->id, 'user_id' => $subscription->user_id,
                        'room_id' => $roomId, 'usage_type' => $subscription->type === 'owner' ? 'listing' : 'contact',
                        'used_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });

        DB::table('enquiries')->orderBy('id')->get()->groupBy(fn ($row) => $row->user_id.'-'.$row->room_id)
            ->each(function ($rows) {
                if ($rows->count() < 2) return;
                $keep = $rows->sortByDesc(fn ($row) => ((int) $row->unlocked * 1000000000) + $row->id)->first();
                DB::table('enquiries')->whereIn('id', $rows->pluck('id')->reject(fn ($id) => $id === $keep->id))->delete();
            });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->unique(['user_id', 'room_id'], 'enquiries_user_room_unique');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', fn (Blueprint $table) => $table->dropUnique('enquiries_user_room_unique'));
        Schema::dropIfExists('subscription_usages');
        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropConstrainedForeignId('payment_id'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('gateway_order_id'));
    }
};

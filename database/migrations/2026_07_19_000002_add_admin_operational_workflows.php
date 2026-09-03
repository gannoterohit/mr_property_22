<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('moderation_status')->default('normal')->after('listing_status')->index();
            $table->text('moderation_note')->nullable()->after('moderation_status');
            $table->timestamp('expires_at')->nullable()->after('moderation_note')->index();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_status')->default('pending')->after('is_verified')->index();
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->string('block_reason')->nullable()->after('is_blocked');
            $table->text('admin_notes')->nullable()->after('block_reason');
        });
        Schema::table('complaints', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->after('priority')->index();
            $table->timestamp('escalated_at')->nullable()->after('due_at');
            $table->string('resolution_category')->nullable()->after('resolution');
            $table->timestamp('reopened_at')->nullable()->after('closed_at');
        });
        DB::table('users')->where('is_verified', true)->update([
            'verification_status' => 'verified',
            'verified_at' => DB::raw('COALESCE(email_verified_at, created_at)'),
        ]);
        DB::table('complaints')->whereNotIn('status', ['resolved', 'rejected', 'closed'])->whereNull('due_at')->update([
            'due_at' => now()->addHours(24),
        ]);
    }
    public function down(): void
    {
        Schema::table('complaints', fn (Blueprint $t) => $t->dropColumn(['due_at','escalated_at','resolution_category','reopened_at']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['verification_status','verified_at','block_reason','admin_notes']));
        Schema::table('rooms', fn (Blueprint $t) => $t->dropColumn(['moderation_status','moderation_note','expires_at']));
    }
};

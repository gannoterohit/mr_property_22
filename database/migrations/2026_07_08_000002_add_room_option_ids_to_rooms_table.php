<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_option_id')->nullable()->after('room_type')->constrained('room_options')->nullOnDelete();
            $table->foreignId('furnishing_option_id')->nullable()->after('furnishing_type')->constrained('room_options')->nullOnDelete();
            $table->foreignId('tenant_option_id')->nullable()->after('tenant_type')->constrained('room_options')->nullOnDelete();
        });

        if (Schema::hasTable('room_options')) {
            $optionIdsByGroup = DB::table('room_options')
                ->select('id', 'group', 'key')
                ->get()
                ->groupBy('group');

            DB::table('rooms')
                ->select('id', 'room_type', 'furnishing_type', 'tenant_type')
                ->orderBy('id')
                ->chunkById(100, function ($rooms) use ($optionIdsByGroup) {
                    foreach ($rooms as $room) {
                        $updates = [];

                        if (!empty($room->room_type)) {
                            $option = $optionIdsByGroup->get('room_type', collect())->firstWhere('key', $room->room_type);
                            if ($option) {
                                $updates['room_type_option_id'] = $option->id;
                            }
                        }

                        if (!empty($room->furnishing_type)) {
                            $option = $optionIdsByGroup->get('furnishing_type', collect())->firstWhere('key', $room->furnishing_type);
                            if ($option) {
                                $updates['furnishing_option_id'] = $option->id;
                            }
                        }

                        if (!empty($room->tenant_type)) {
                            $option = $optionIdsByGroup->get('tenant_type', collect())->firstWhere('key', $room->tenant_type);
                            if ($option) {
                                $updates['tenant_option_id'] = $option->id;
                            }
                        }

                        if (!empty($updates)) {
                            DB::table('rooms')->where('id', $room->id)->update($updates);
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_type_option_id');
            $table->dropConstrainedForeignId('furnishing_option_id');
            $table->dropConstrainedForeignId('tenant_option_id');
        });
    }
};

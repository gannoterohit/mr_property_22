<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->whereIn('key', ['commission_percent', 'service_charge'])
            ->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insertOrIgnore([
            [
                'key' => 'commission_percent',
                'value' => '10',
                'type' => 'number',
                'group' => 'business',
                'description' => 'Legacy booking commission percentage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'service_charge',
                'value' => '200',
                'type' => 'number',
                'group' => 'business',
                'description' => 'Legacy fixed booking service charge',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->insertOrIgnore([
            [
                'key' => 'referral_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'business',
                'description' => 'Enable Referral System (1 to enable, 0 to disable)'
            ],
            [
                'key' => 'wallet_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'business',
                'description' => 'Enable Wallet System'
            ],
            [
                'key' => 'promo_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'business',
                'description' => 'Enable Promo Codes'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->whereIn('key', ['referral_enabled', 'wallet_enabled', 'promo_enabled'])->delete();
    }
};

<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->whereIn('key', Setting::SECRET_KEYS)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->orderBy('id')
            ->each(function ($setting): void {
                try {
                    Crypt::decryptString($setting->value);
                } catch (\Throwable) {
                    DB::table('settings')->where('id', $setting->id)->update([
                        'value' => Crypt::encryptString($setting->value),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Secrets intentionally remain encrypted when rolling back.
    }
};

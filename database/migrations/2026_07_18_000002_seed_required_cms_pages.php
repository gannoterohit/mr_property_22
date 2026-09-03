<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = config('cms.defaults', []);

        foreach ($defaults as $key => $value) {
            $existing = DB::table('settings')->where('key', $key)->first();
            $replacePlaceholder = in_array($key, ['terms_content', 'privacy_content'], true)
                && $existing && strlen((string) $existing->value) < 300;

            if (!$existing) {
                DB::table('settings')->insert([
                    'key' => $key, 'value' => $value, 'type' => 'textarea',
                    'group' => 'pages', 'created_at' => now(), 'updated_at' => now(),
                ]);
            } elseif ($replacePlaceholder) {
                DB::table('settings')->where('key', $key)->update(['value' => $value, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['owner_guidelines_content', 'user_guidelines_content'])->delete();
    }
};

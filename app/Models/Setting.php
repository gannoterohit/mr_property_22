<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    public const SECRET_KEYS = [
        'mail_password',
        'razorpay_secret',
        'razorpay_webhook_secret',
        'google_maps_api_key',
        'firebase_server_key',
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public function getValueAttribute($value)
    {
        if (! in_array($this->key, self::SECRET_KEYS, true) || $value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Existing installations may still contain legacy plaintext. It is
            // encrypted automatically the next time the setting is saved.
            return $value;
        }
    }

    public function setValueAttribute($value): void
    {
        if (! in_array($this->key, self::SECRET_KEYS, true) || $value === null || $value === '') {
            $this->attributes['value'] = $value;
            return;
        }

        try {
            Crypt::decryptString((string) $value);
            $this->attributes['value'] = $value;
        } catch (DecryptException) {
            $this->attributes['value'] = Crypt::encryptString((string) $value);
        }
    }

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function set($key, $value)
    {
        if (!Schema::hasTable('settings')) {
            return null;
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Dynamically apply mail settings from the database to Laravel's configuration
     */
    public static function setMailConfig()
    {
        $host = trim(self::get('mail_host', ''));
        $port = self::get('mail_port', 587);
        $username = trim(self::get('mail_username', ''));
        $password = trim(self::get('mail_password', ''));
        $from_address = trim(self::get('contact_email', 'hello@example.com'));
        $from_name = self::get('website_name', 'RoomRental');

        if ($host && $username && $password) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => ($port == 465) ? 'ssl' : 'tls',
                'mail.from.address' => $from_address,
                'mail.from.name' => $from_name,
                'mail.default' => 'smtp',
            ]);
        }
    }
}


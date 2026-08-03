<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS OTP to a phone number based on configured SMS Gateway provider.
     */
    public static function sendOtp(string $phone, string $otp): bool
    {
        $provider = Setting::get('sms_gateway', 'log');
        $apiKey   = Setting::get('sms_api_key');

        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        // Mode 1: Log / Demo Mode
        if ($provider === 'log' || empty($apiKey)) {
            Log::info("SMS OTP [DEMO/LOG] sent to +{$cleanPhone}: OTP code is {$otp}");
            return true;
        }

        // Mode 2: MSG91 Integration
        if ($provider === 'msg91') {
            return self::sendViaMsg91($cleanPhone, $otp, $apiKey);
        }

        // Mode 3: Twilio Integration
        if ($provider === 'twilio') {
            return self::sendViaTwilio($phone, $otp, $apiKey);
        }

        // Mode 4: Fast2SMS Integration
        if ($provider === 'fast2sms') {
            return self::sendViaFast2Sms($cleanPhone, $otp, $apiKey);
        }

        Log::info("SMS OTP sent to +{$cleanPhone}: {$otp} via {$provider}");
        return true;
    }

    private static function sendViaMsg91(string $phone, string $otp, string $apiKey): bool
    {
        try {
            $templateId = Setting::get('sms_dlt_te_id');
            $response = Http::withHeaders([
                'authkey'      => $apiKey,
                'content-type' => 'application/json',
            ])->post('https://control.msg91.com/api/v5/otp', [
                'template_id' => $templateId,
                'mobile'      => $phone,
                'otp'         => $otp,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SmsService MSG91 exception: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaTwilio(string $phone, string $otp, string $apiKey): bool
    {
        try {
            $sid  = Setting::get('sms_sender_id');
            $from = Setting::get('sms_dlt_te_id');
            $response = Http::withBasicAuth($sid, $apiKey)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To'   => '+' . preg_replace('/[^0-9]/', '', $phone),
                    'From' => $from,
                    'Body' => "Your ApnaNest login OTP is {$otp}. Valid for 10 minutes.",
                ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SmsService Twilio exception: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaFast2Sms(string $phone, string $otp, string $apiKey): bool
    {
        try {
            $phone10 = substr(preg_replace('/[^0-9]/', '', $phone), -10);
            $response = Http::withHeaders([
                'authorization' => $apiKey,
            ])->post('https://www.fast2sms.com/dev/bulkV2', [
                'variables_values' => $otp,
                'route'            => 'otp',
                'numbers'          => $phone10,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SmsService Fast2SMS exception: ' . $e->getMessage());
            return false;
        }
    }
}

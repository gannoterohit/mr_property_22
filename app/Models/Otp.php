<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'code', 'expires_at', 'used'];

    /**
     * Generate a new OTP for the given email
     */
    public static function generate(string $email): string
    {
        // Delete any existing OTPs for this email
        self::where('email', $email)->delete();
        
        // Generate a 6-digit OTP
        $code = random_int(100000, 999999);
        
        // Create new OTP that expires in 10 minutes
        self::create([
            'email' => $email,
            'code' => Hash::make((string) $code),
            'expires_at' => now()->addMinutes(10),
        ]);
        
        return $code;
    }
    
    /**
     * Verify if the OTP is valid for the given email
     */
    public static function verify(string $email, string $code): bool
    {
        return DB::transaction(function () use ($email, $code): bool {
            $otp = self::where('email', $email)
                ->where('expires_at', '>', now())
                ->where('used', false)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                return false;
            }

            $valid = str_starts_with($otp->code, '$')
                ? Hash::check($code, $otp->code)
                : hash_equals((string) $otp->code, $code);

            if (! $valid) {
                return false;
            }

            $otp->update(['used' => true]);

            return true;
        });
    }
}

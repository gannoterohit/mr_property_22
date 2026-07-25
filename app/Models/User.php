<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'avatar',
        'password',
        'phone',
        'role',
        'admin_role_id',
        'is_staff_active',
        'last_admin_login_at',
        'wallet',
        'wallet_balance',
        'free_unlocks',
        'is_verified',
        'verification_status',
        'verified_at',
        'is_blocked',
        'block_reason',
        'admin_notes',
        'referral_code',
        'referred_by_id',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->referral_code) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_blocked' => 'boolean',
            'wallet_balance' => 'decimal:2',
            'is_staff_active' => 'boolean',
            'last_admin_login_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function rooms() {
        return $this->hasMany(Room::class);
    }
    public function bookings() {
        return $this->hasMany(Booking::class);
    }
    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }
    public function payments() { return $this->hasMany(Payment::class); }
    public function enquiries() { return $this->hasMany(Enquiry::class); }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }


    public function cityAlerts()
    {
        return $this->hasMany(CityAlert::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function adminActivities()
    {
        return $this->hasMany(AdminActivityLog::class, 'actor_id');
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->role !== 'admin' || !$this->is_staff_active) return false;
        if (!$this->admin_role_id) return true; // Existing administrators remain safe super admins.
        $permissions = $this->adminRole?->permissions ?? [];
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function hasInWishlist($roomId)
    {
        return $this->wishlists()->where('room_id', $roomId)->exists();
    }
}

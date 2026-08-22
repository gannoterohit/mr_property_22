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
        'fcm_token',
        'web_push_token',
        'provider',
        'provider_id',
        'agency_name',
        'agency_address',
        'agency_gst',
        'broker_license',
        'broker_verification_status',
        'broker_verified_at',
        'is_broker_active',
        'broker_approved_at',
        'broker_rejected_reason',
        'broker_total_listings',
        'broker_active_listings',
        'broker_featured_listings',
        'broker_subscription_expires_at',
        'broker_subscription_listings_limit',
        'broker_subscription_listings_used',
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
            'broker_verified_at' => 'datetime',
            'is_broker_active' => 'boolean',
            'broker_approved_at' => 'datetime',
            'broker_subscription_expires_at' => 'datetime',
        ];
    }

    public function rooms() {
        return $this->hasMany(Room::class);
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

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function brokerProperties()
    {
        return $this->hasMany(Room::class, 'broker_id');
    }

    public function brokerSubscription()
    {
        return $this->hasOne(BrokerSubscription::class, 'broker_id')->where('status', 'active')->latest();
    }

    public function brokerListingCredits()
    {
        return $this->hasMany(BrokerListingCredit::class, 'broker_id');
    }

    public function brokerPayments()
    {
        return $this->hasMany(BrokerPayment::class, 'broker_id');
    }

    public function brokerTransactions()
    {
        return $this->hasMany(BrokerTransaction::class, 'broker_id');
    }

    public function brokerWallet()
    {
        return $this->hasOne(BrokerWallet::class, 'broker_id');
    }

    public function brokerRanking()
    {
        return $this->hasOne(BrokerRanking::class, 'broker_id');
    }

    public function isBroker(): bool
    {
        return $this->role === 'broker';
    }

    public function getBrokerVerificationStatusAttribute($value)
    {
        return $value ?: $this->broker_verification_status;
    }
}

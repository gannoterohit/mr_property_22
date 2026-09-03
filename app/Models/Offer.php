<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Offer extends Model
{
    protected $fillable = [
        'title',
        'description',
        'code',
        'discount_type',
        'discount_value',
        'max_discount_cap',
        'min_order_value',
        'max_uses',
        'uses_count',
        'per_user_limit',
        'applicable_for',
        'target_audience',
        'show_as_banner',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'show_as_banner'   => 'boolean',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'discount_value'   => 'decimal:2',
        'max_discount_cap' => 'decimal:2',
        'min_order_value'  => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            });
    }

    public function scopePubliclyVisible($query)
    {
        return $query->active()->where('show_as_banner', true);
    }

    // ─── Validity Methods ─────────────────────────────────────────────────────

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_date && now()->lt($this->start_date)) return false;
        if ($this->end_date && now()->gt($this->end_date->copy()->endOfDay())) return false;
        return true;
    }

    public function hasUsageLeft(): bool
    {
        if ($this->max_uses === null) return true;
        return $this->uses_count < $this->max_uses;
    }

    public function hasUserLimitLeft(int $userId): bool
    {
        $userCount = $this->usages()->where('user_id', $userId)->count();
        return $userCount < $this->per_user_limit;
    }

    public function canBeAppliedToAmount(float $amount): bool
    {
        return $amount >= (float) $this->min_order_value;
    }

    /**
     * Check if coupon can be used by a given user for a given context
     */
    public function canBeUsedBy(int $userId, string $context = 'all', float $amount = 0): array
    {
        if (!$this->isCurrentlyActive()) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        if (!$this->hasUsageLeft()) {
            return ['valid' => false, 'message' => 'This coupon has reached its maximum usage limit.'];
        }

        if (!$this->hasUserLimitLeft($userId)) {
            return ['valid' => false, 'message' => 'You have already used this coupon the maximum number of times.'];
        }

        if ($this->applicable_for !== 'all' && $this->applicable_for !== $context) {
            $labels = [
                'owner_plans'  => 'owner subscription plans',
                'user_plans'   => 'user contact plans',
                'broker_plans' => 'broker plans',
                'unlocks'      => 'contact unlocks',
            ];
            return ['valid' => false, 'message' => 'This coupon is only valid for ' . ($labels[$this->applicable_for] ?? $this->applicable_for) . '.'];
        }

        if (!$this->canBeAppliedToAmount($amount)) {
            return ['valid' => false, 'message' => 'Minimum order value of ₹' . number_format($this->min_order_value, 0) . ' required to use this coupon.'];
        }

        return ['valid' => true, 'message' => 'Coupon applied!'];
    }

    /**
     * Calculate the actual discount for a given amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'flat') {
            return min((float) $this->discount_value, $amount);
        }

        // Percentage
        $discount = $amount * ((float) $this->discount_value / 100);
        if ($this->max_discount_cap !== null) {
            $discount = min($discount, (float) $this->max_discount_cap);
        }
        return round($discount, 2);
    }

    /**
     * Get the final payable amount after discount
     */
    public function getFinalAmount(float $amount): float
    {
        return max(0, $amount - $this->calculateDiscount($amount));
    }

    /**
     * Increment usage count atomically
     */
    public function recordUsage(int $userId, string $usedInType, int $usedInId, float $originalAmount, float $discountAmount): CouponUsage
    {
        $this->increment('uses_count');

        return CouponUsage::create([
            'offer_id'      => $this->id,
            'user_id'       => $userId,
            'used_in_type'  => $usedInType,
            'used_in_id'    => $usedInId,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount'  => max(0, $originalAmount - $discountAmount),
        ]);
    }

    /**
     * Human-readable discount label (e.g. "20% OFF" or "₹200 OFF")
     */
    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            $label = (int) $this->discount_value . '% OFF';
            if ($this->max_discount_cap) {
                $label .= ' (max ₹' . number_format($this->max_discount_cap, 0) . ')';
            }
            return $label;
        }
        return '₹' . number_format($this->discount_value, 0) . ' OFF';
    }

    public function getApplicableForLabelAttribute(): string
    {
        return match ($this->applicable_for) {
            'owner_plans'  => 'Owner Plans',
            'user_plans'   => 'User Plans',
            'broker_plans' => 'Broker Plans',
            'unlocks'      => 'Contact Unlocks',
            default        => 'All Services',
        };
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) return 'Inactive';
        if ($this->start_date && $this->start_date->startOfDay()->gt(now())) return 'Scheduled';
        if ($this->end_date && $this->end_date->endOfDay()->lt(now())) return 'Expired';
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return 'Exhausted';
        return 'Live';
    }
}

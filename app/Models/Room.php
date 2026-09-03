<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'slug',
        'user_id',
        'title',
        'description',
        'type',
        'property_type_id',
        'property_category_id',
        'room_type_option_id',
        'furnishing_option_id',
        'tenant_option_id',
        'amenities',
        'rent',
        'deposit',
        'area_sqft',
        'city',
        'state',
        'country',
        'address',
        'latitude',
        'longitude',
        'availability_from',
        'status',
        'listing_status',
        'video_url',
        'video',
        'photo',
        'photos',
        'landmarks',
        'is_featured',
        'listing_fee_paid',
        'listing_payment_id',
        'listing_type',
        'broker_fee',
        'broker_id',
        'expires_at',
        'moderation_status',
        'moderation_note',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'listing_fee_paid' => 'boolean',
        'area_sqft' => 'decimal:2',
        'photos' => 'array',
        'amenities' => 'array',
        'landmarks' => 'array',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function expiresInDays(): ?int
    {
        if (!$this->expires_at) return null;
        return (int) now()->diffInDays($this->expires_at, false);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)
            ->orWhere('id', $value)
            ->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (!$room->slug) {
                $room->slug = static::generateUniqueSlug($room->title);
            }
        });

        static::updating(function ($room) {
            if ($room->isDirty('title') && !$room->isDirty('slug')) {
                $room->slug = static::generateUniqueSlug($room->title);
            }
        });

        static::deleting(function ($room) {
            foreach ($room->publicMediaPaths() as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public static function generateUniqueSlug($title)
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    
    public function owner() {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Alias for owner relationship
     */
    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function propertyCategory()
    {
        return $this->belongsTo(PropertyCategory::class, 'property_category_id');
    }

    public function roomTypeOption()
    {
        return $this->belongsTo(RoomOption::class, 'room_type_option_id');
    }

    public function furnishingOption()
    {
        return $this->belongsTo(RoomOption::class, 'furnishing_option_id');
    }

    public function tenantOption()
    {
        return $this->belongsTo(RoomOption::class, 'tenant_option_id');
    }


    public function complaints() {
        return $this->hasMany(Complaint::class);
    }
    
    public function rejectionReasons()
    {
        return $this->belongsToMany(RejectionReason::class, 'room_rejection_reason');
    }

    public function getPhotoUrlAttribute()
    {
        $url = null;
        if (!$this->photo) {
            if ($this->photos && count($this->photos) > 0) {
                $url = $this->photos[0];
            } else {
                return asset('assets/images/default-room.svg');
            }
        } else {
            $url = $this->photo;
        }

        $finalUrl = $this->resolvePublicMediaUrl($url);

        // Optimize Unsplash URLs
        if (str_contains($finalUrl, 'images.unsplash.com')) {
            if (!str_contains($finalUrl, '?') && !str_contains($finalUrl, '&')) {
                $finalUrl .= '?auto=format&fit=crop&w=400&q=60&fm=webp';
            } elseif (str_contains($finalUrl, 'w=800')) {
                $finalUrl = str_replace('w=800', 'w=400', $finalUrl);
                if (!str_contains($finalUrl, 'fm=')) $finalUrl .= '&fm=webp';
                if (!str_contains($finalUrl, 'q=')) $finalUrl .= '&q=60';
            }
        }

        return $finalUrl;
    }

    public function getPhotoUrlsAttribute()
    {
        $urls = [];
        if ($this->photos && is_array($this->photos)) {
            foreach ($this->photos as $photo) {
                $urls[] = $this->resolvePublicMediaUrl($photo);
            }
        }
        
        if (empty($urls) && $this->photo) {
            $urls[] = $this->resolvePublicMediaUrl($this->photo);
        }

        return $urls;
    }

    public function publicMediaPaths(): array
    {
        $paths = array_merge([$this->photo], $this->photos ?: [], [$this->video]);

        return collect($paths)
            ->filter(fn ($path) => is_string($path) && $path !== '' && ! preg_match('/^https?:\/\//', $path))
            ->map(function ($path) {
                $path = ltrim($path, '/');
                return str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function resolvePublicMediaUrl(?string $path): string
    {
        if (!$path) {
            return asset('assets/images/default-room.svg');
        }

        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('assets/images/default-room.svg');
        }

        return asset('storage/' . $path);
    }

    public function roomTypeLabel(): string
    {
        if ($this->relationLoaded('roomTypeOption') && $this->roomTypeOption) {
            return $this->roomTypeOption->label;
        }

        return RoomOption::getLabel('room_type', $this->room_type_option_id);
    }

    public function furnishingTypeLabel(): string
    {
        if ($this->relationLoaded('furnishingOption') && $this->furnishingOption) {
            return $this->furnishingOption->label;
        }

        return RoomOption::getLabel('furnishing_type', $this->furnishing_option_id);
    }

    public function tenantTypeLabel(): string
    {
        if ($this->relationLoaded('tenantOption') && $this->tenantOption) {
            return $this->tenantOption->label;
        }

        return RoomOption::getLabel('tenant_type', $this->tenant_option_id);
    }

    public function scopePublicVisible($query)
    {
        return $query->where('status', 'active')
            ->where('listing_status', 'approved')
            ->where('listing_fee_paid', true)
            ->whereHas('propertyType', fn ($type) => $type->active())
            ->whereHas('propertyCategory', fn ($category) => $category->publicSelectable())
            ->where(fn ($room) => $room->whereNull('room_type_option_id')->orWhereHas('roomTypeOption', fn ($option) => $option->active()))
            ->where(fn ($room) => $room->whereNull('furnishing_option_id')->orWhereHas('furnishingOption', fn ($option) => $option->active()))
            ->where(fn ($room) => $room->whereNull('tenant_option_id')->orWhereHas('tenantOption', fn ($option) => $option->active()));
    }

    public function publicAmenities(): array
    {
        $activeLabels = RoomOption::activeLabelsFor('amenity')
            ->map(fn ($label) => mb_strtolower((string) $label))
            ->all();

        return collect($this->amenities ?: [])
            ->filter(fn ($amenity) => in_array(mb_strtolower((string) $amenity), $activeLabels, true))
            ->values()
            ->all();
    }
}

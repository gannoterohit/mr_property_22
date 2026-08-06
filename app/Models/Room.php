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
    ];

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
                return asset('storage/default-room.jpg');
            }
        } else {
            $url = $this->photo;
        }

        $finalUrl = (preg_match('/^https?:\/\//', $url)) ? $url : asset('storage/' . $url);

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
                $urls[] = (str_starts_with($photo, 'http')) ? $photo : asset('storage/' . $photo);
            }
        }
        
        if (empty($urls) && $this->photo) {
            $urls[] = (str_starts_with($this->photo, 'http')) ? $this->photo : asset('storage/' . $this->photo);
        }

        return $urls;
    }

    public function roomTypeLabel(): string
    {
        return RoomOption::getLabel('room_type', $this->room_type_option_id);
    }

    public function furnishingTypeLabel(): string
    {
        return RoomOption::getLabel('furnishing_type', $this->furnishing_option_id);
    }

    public function tenantTypeLabel(): string
    {
        return RoomOption::getLabel('tenant_type', $this->tenant_option_id);
    }
}

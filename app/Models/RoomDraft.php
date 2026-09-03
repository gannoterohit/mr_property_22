<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RoomDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'step',
        'data',
        'photos',
        'video_path',
        'video_url',
        'is_published',
        'last_saved_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'photos' => 'array',
        'is_published' => 'boolean',
        'last_saved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STEP_NAMES = [
        1 => 'Basic Details',
        2 => 'Location',
        3 => 'Property Details',
        4 => 'Amenities & Rules',
        5 => 'Pricing & Media',
        6 => 'Review & Publish',
    ];

    public const TOTAL_STEPS = 6;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function touchSaved(): void
    {
        $this->forceFill([
            'last_saved_at' => now(),
            'expires_at' => now()->addDays(30),
        ])->save();
    }

    public function progressPercent(): int
    {
        return (int) round(($this->step / self::TOTAL_STEPS) * 100);
    }

    public function displayTitle(): string
    {
        return $this->title ?: ($this->data['title'] ?? 'Untitled Draft');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RoomOption extends Model
{
    protected $fillable = [
        'group',
        'key',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $clearOptionCache = function (self $option): void {
            foreach (array_unique(array_filter([
                $option->key,
                $option->getOriginal('key'),
            ])) as $key) {
                Cache::forget("room_option_id:{$option->group}:{$key}");

                if ($option->wasChanged('group')) {
                    Cache::forget("room_option_id:{$option->getOriginal('group')}:{$key}");
                }
            }
        };

        static::saved($clearOptionCache);
        static::deleted($clearOptionCache);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function optionsFor(string $group, $selectedValue = null): Collection
    {
        $options = static::active()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'key', 'label']);

    if ($selectedValue !== null) {
        $matches = is_numeric($selectedValue)
            ? fn ($option) => $option->id == $selectedValue
            : fn ($option) => $option->key === $selectedValue;

        if (!$options->contains($matches)) {
            // Keep an inactive option visible when editing an existing room.
            // Owners can see the saved value, but active-only validation prevents
            // selecting a retired option for a new room or changing back to it.
            $selectedOption = static::resolveOption($group, $selectedValue, true);

            $options->prepend((object) [
                'id' => $selectedOption?->id,
                'key' => $selectedOption?->key ?? $selectedValue,
                'label' => $selectedOption?->label ?? static::formatLabel($group, $selectedValue),
            ]);
        }
    }

        return $options;
    }

    public static function validIdsFor(string $group): array
    {
        return static::optionsFor($group)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public static function idForKey(string $group, $key): ?int
    {
        if ($key === null || $key === '') {
            return null;
        }

        return \Illuminate\Support\Facades\Cache::remember(
            "room_option_id:{$group}:{$key}",
            86400,
            fn () => static::active()->where('group', $group)->where('key', (string) $key)->value('id')
        );
    }

    public static function resolveId(string $group, $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $option = static::active()->where('group', $group)->where('key', (string) $value)->first();

        return $option?->id;
    }

    public static function resolveOption(string $group, $value, bool $includeInactive = false): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $query = static::query()->where('group', $group);

        if (!$includeInactive) {
            $query->active();
        }

        return is_numeric($value)
            ? $query->where('id', (int) $value)->first()
            : $query->where('key', (string) $value)->first();
    }

    public static function getLabel(string $group, $value, ?string $fallback = null): string
    {
        if ($value === null || $value === '') {
            return $fallback ?? 'N/A';
        }

        // Existing listings must retain a human-readable label after an admin
        // retires the option.
        $option = static::resolveOption($group, $value, true);

        if ($option) {
            return $option->label;
        }

        return static::formatLabel($group, $value, $fallback);
    }

    public static function formatLabel(string $group, $value, ?string $fallback = null): string
    {
        if ($value === null || $value === '') {
            return $fallback ?? 'N/A';
        }

        $fallbackLabel = $fallback ?? (is_string($value) ? str_replace('_', ' ', $value) : (string) $value);

        return ucwords((string) $fallbackLabel);
    }
}

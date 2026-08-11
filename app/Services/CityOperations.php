<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class CityOperations
{
    public static function resolve(?string $requestedCity, ?string $sessionCity = null): array
    {
        $detectedName = trim((string) ($requestedCity ?: $sessionCity));

        if (!Schema::hasTable('cities')) {
            return [
                'detectedCityName' => $detectedName,
                'activeCityName' => $detectedName ?: null,
                'defaultCityName' => 'Bhopal',
                'isFallback' => false,
                'launchingSoonCityName' => null,
            ];
        }

        $defaultCity = City::defaultCity();
        $detectedCity = City::findByName($detectedName);

        if ($detectedName !== '' && $detectedCity?->is_active) {
            return [
                'detectedCityName' => $detectedCity->name,
                'activeCityName' => $detectedCity->name,
                'defaultCityName' => $defaultCity?->name,
                'isFallback' => false,
                'launchingSoonCityName' => null,
            ];
        }

        $fallbackName = $defaultCity?->name ?: $detectedName;

        return [
            'detectedCityName' => $detectedName,
            'activeCityName' => $fallbackName,
            'defaultCityName' => $defaultCity?->name,
            'isFallback' => $detectedName !== '' && $fallbackName !== '' && strcasecmp($detectedName, $fallbackName) !== 0,
            'launchingSoonCityName' => $detectedName !== '' ? ($detectedCity?->name ?: $detectedName) : null,
        ];
    }

    public static function applyRoomCity(Builder $query, array $cityContext): Builder
    {
        if (!empty($cityContext['activeCityName'])) {
            $query->where('city', 'like', '%' . $cityContext['activeCityName'] . '%');
        }

        return $query;
    }

    public static function selectorCities()
    {
        if (!Schema::hasTable('cities')) {
            return collect();
        }

        return Cache::remember('cities.selector.active', 3600, function () {
            return City::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPlatformController extends BaseApiController
{
    public function cities(Request $request)
    {
        $query = City::query()->orderBy('sort_order')->orderBy('name');
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        return $this->sendSuccess($query->get());
    }

    public function storeCity(Request $request)
    {
        $city = City::create($this->cityData($request));

        return $this->sendSuccess($city, 'City created', 201);
    }

    public function updateCity(Request $request, City $city)
    {
        $city->update($this->cityData($request, $city));

        return $this->sendSuccess($city->fresh(), 'City updated');
    }

    public function destroyCity(City $city)
    {
        if ($city->is_default) {
            return $this->sendError('The default city cannot be deleted.', [], 422);
        }
        $city->delete();

        return $this->sendSuccess([], 'City deleted');
    }

    public function homePage()
    {
        return $this->sendSuccess(
            Setting::where('key', 'like', 'home_%')->orderBy('key')->pluck('value', 'key')
        );
    }

    public function updateHomePage(Request $request)
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            if (!str_starts_with($key, 'home_')) {
                return $this->sendError("Invalid home-page setting: {$key}", [], 422);
            }
            Setting::set($key, $value ?? '');
        }

        return $this->sendSuccess($data['settings'], 'Home page settings updated');
    }

    private function cityData(Request $request, ?City $city = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('cities', 'name')->ignore($city?->id)],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('cities', 'slug')->ignore($city?->id)],
            'state' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}

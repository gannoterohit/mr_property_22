<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\City;
use App\Models\CityAlert;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Room;
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
        if (Room::where('city', $city->name)->exists() || CityAlert::where('city', $city->name)->exists()) {
            return $this->sendError('This city is in use. Deactivate it instead of deleting it.', [], 422);
        }
        $city->delete();

        return $this->sendSuccess([], 'City deleted');
    }

    public function toggleCity(City $city)
    {
        $city->update(['is_active' => ! $city->is_active]);

        return $this->sendSuccess(['is_active' => $city->is_active], 'City status updated');
    }

    public function propertyTypes(Request $request)
    {
        $query = PropertyType::withCount('categories')->orderBy('name');
        if ($request->filled('active')) {
            $query->where('status', $request->boolean('active'));
        }

        return $this->sendSuccess($query->get());
    }

    public function storePropertyType(Request $request)
    {
        $type = PropertyType::create($this->propertyTypeData($request));

        return $this->sendSuccess($type, 'Property type created', 201);
    }

    public function updatePropertyType(Request $request, PropertyType $propertyType)
    {
        $propertyType->update($this->propertyTypeData($request, $propertyType));

        return $this->sendSuccess($propertyType->fresh('categories'), 'Property type updated');
    }

    public function togglePropertyType(PropertyType $propertyType)
    {
        $propertyType->update(['status' => ! $propertyType->status]);

        return $this->sendSuccess(['status' => $propertyType->status], 'Property type status updated');
    }

    public function destroyPropertyType(PropertyType $propertyType)
    {
        if ($propertyType->categories()->exists() || Room::where('property_type_id', $propertyType->id)->exists()) {
            return $this->sendError('This property type is in use. Deactivate it instead of deleting it.', [], 422);
        }

        $propertyType->delete();

        return $this->sendSuccess([], 'Property type deleted');
    }

    public function propertyCategories(Request $request)
    {
        $query = PropertyCategory::with('propertyType')->orderBy('property_type_id')->orderBy('name');
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->integer('property_type_id'));
        }
        if ($request->filled('active')) {
            $query->where('status', $request->boolean('active'));
        }

        return $this->sendSuccess($query->get());
    }

    public function storePropertyCategory(Request $request)
    {
        $category = PropertyCategory::create($this->propertyCategoryData($request));

        return $this->sendSuccess($category->load('propertyType'), 'Property category created', 201);
    }

    public function updatePropertyCategory(Request $request, PropertyCategory $propertyCategory)
    {
        $propertyCategory->update($this->propertyCategoryData($request, $propertyCategory));

        return $this->sendSuccess($propertyCategory->fresh('propertyType'), 'Property category updated');
    }

    public function togglePropertyCategory(PropertyCategory $propertyCategory)
    {
        $propertyCategory->update(['status' => ! $propertyCategory->status]);

        return $this->sendSuccess(['status' => $propertyCategory->status], 'Property category status updated');
    }

    public function destroyPropertyCategory(PropertyCategory $propertyCategory)
    {
        if (Room::where('property_category_id', $propertyCategory->id)->exists()) {
            return $this->sendError('This property category is in use. Deactivate it instead of deleting it.', [], 422);
        }

        $propertyCategory->delete();

        return $this->sendSuccess([], 'Property category deleted');
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
            'image_url' => ['nullable', 'url', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function propertyTypeData(Request $request, ?PropertyType $propertyType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('property_types', 'slug')->ignore($propertyType?->id)],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->has('status') ? $request->boolean('status') : ($propertyType?->status ?? true);

        return $data;
    }

    private function propertyCategoryData(Request $request, ?PropertyCategory $propertyCategory = null): array
    {
        $data = $request->validate([
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('property_categories', 'slug')->ignore($propertyCategory?->id)],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->has('status') ? $request->boolean('status') : ($propertyCategory?->status ?? true);

        return $data;
    }
}

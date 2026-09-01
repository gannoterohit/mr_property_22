<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::orderByDesc('is_default')->orderByDesc('is_active')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.cities.index', compact('cities'));
    }

    public function create()
    {
        return view('admin.cities.create');
    }

    public function edit(City $city)
    {
        return view('admin.cities.edit', compact('city'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:cities,name',
            'state' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'image_url' => 'nullable|url|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        $uploadedImages = $this->handleMultipleCityImageUploads($request);
        $data['hero_images'] = !empty($uploadedImages) ? $uploadedImages : null;
        $data['image_url'] = $uploadedImages[0] ?? null;

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        City::create($data);

        return redirect()->route('admin.cities.index')->with('success', 'City added successfully.');
    }

    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:cities,name,' . $city->id,
            'state' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'existing_hero_images' => 'nullable|array',
            'image_url' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        // Retain remaining existing images
        $keptImages = $request->input('existing_hero_images', []);
        if (!is_array($keptImages)) {
            $keptImages = [];
        }

        // Delete removed images from storage
        $currentImages = is_array($city->hero_images) ? $city->hero_images : ($city->image_url ? [$city->image_url] : []);
        foreach ($currentImages as $oldImg) {
            if (!in_array($oldImg, $keptImages, true)) {
                $this->deleteStoredCityImage($oldImg);
            }
        }

        // Upload newly added images
        $newlyUploaded = $this->handleMultipleCityImageUploads($request);
        $allImages = array_values(array_filter(array_merge($keptImages, $newlyUploaded)));

        if (!empty($allImages)) {
            $data['hero_images'] = $allImages;
            $data['image_url'] = $allImages[0];
        } else {
            $data['hero_images'] = null;
            $data['image_url'] = null;
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $city->is_active;
        $data['is_default'] = $request->boolean('is_default');

        $city->update($data);

        if ($request->header('Referer') && str_contains($request->header('Referer'), '/edit')) {
            return redirect()->route('admin.cities.edit', $city)->with('success', 'City updated successfully.');
        }

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function toggleStatus(City $city)
    {
        if ($city->is_default && $city->is_active) {
            return back()->with('error', 'Default city cannot be deactivated. Set another active city as default first.');
        }

        $city->update(['is_active' => !$city->is_active]);

        return back()->with('success', $city->is_active ? 'City activated successfully.' : 'City deactivated successfully.');
    }

    public function destroy(City $city)
    {
        if ($city->is_default) {
            return back()->with('error', 'Default city cannot be deleted. Set another city as default first.');
        }

        $hasListings = \App\Models\Room::where('city', $city->name)->exists();
        $hasAlerts = \App\Models\CityAlert::where('city', $city->name)->exists();

        if ($hasListings || $hasAlerts) {
            return back()->with('error', 'This city has related listings or alerts. Deactivate it instead of deleting.');
        }

        if (is_array($city->hero_images)) {
            foreach ($city->hero_images as $img) {
                $this->deleteStoredCityImage($img);
            }
        }
        $this->deleteStoredCityImage($city->image_url);
        $city->delete();

        return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully.');
    }

    protected function handleMultipleCityImageUploads(Request $request): array
    {
        $uploaded = [];

        // Multiple files via images[]
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = \App\Services\ImageOptimizer::optimizeToPublicPath(
                        $file,
                        'city_hero',
                        'uploads/cities'
                    );
                    if ($path) {
                        $uploaded[] = $path;
                    }
                }
            }
        }

        // Single file fallback via image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                $path = \App\Services\ImageOptimizer::optimizeToPublicPath(
                    $file,
                    'city_hero',
                    'uploads/cities'
                );
                if ($path && !in_array($path, $uploaded, true)) {
                    $uploaded[] = $path;
                }
            }
        }

        return $uploaded;
    }

    protected function deleteStoredCityImage(?string $imageUrl): void
    {
        if (empty($imageUrl) || filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return;
        }

        $normalized = ltrim($imageUrl, '/');
        $paths = [$normalized];

        if (str_starts_with($normalized, 'storage/')) {
            $paths[] = substr($normalized, 8);
        } else {
            $paths[] = 'storage/' . $normalized;
        }

        foreach ($paths as $path) {
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                @unlink($publicPath);
            }

            $storagePath = storage_path('app/public/' . ltrim($path, '/'));
            if (file_exists($storagePath)) {
                @unlink($storagePath);
            }
        }
    }
}

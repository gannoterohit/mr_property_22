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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:cities,name',
            'state' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'image_url' => 'nullable|url|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        $data['image_url'] = $this->handleCityImageUpload($request);

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
            'image_url' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->handleCityImageUpload($request, $city);
        } elseif ($request->filled('image_url')) {
            $data['image_url'] = $request->image_url;
        } else {
            $data['image_url'] = $city->image_url;
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        $city->update($data);

        return back()->with('success', 'City updated successfully.');
    }

    public function toggleStatus(City $city)
    {
        if ($city->is_default && $city->is_active) {
            return back()->with('error', 'Default city cannot be deactivated. Set another active city as default first.');
        }

        $city->update(['is_active' => !$city->is_active]);

        return back()->with('success', $city->is_active ? 'City activated successfully.' : 'City deactivated successfully.');
    }

    protected function handleCityImageUpload(Request $request, ?City $city = null): ?string
    {
        if ($request->hasFile('image')) {
            $this->deleteStoredCityImage($city?->image_url);

            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = Str::uuid() . '.' . $extension;
            $destination = public_path('uploads/cities');

            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);

            return 'uploads/cities/' . $filename;
        }

        if ($request->filled('image_url')) {
            return $request->image_url;
        }

        return $city?->image_url;
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

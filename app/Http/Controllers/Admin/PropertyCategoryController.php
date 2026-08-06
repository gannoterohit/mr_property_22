<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyCategoryController extends Controller
{
    public function index()
    {
        $categories = PropertyCategory::with('propertyType')->orderBy('property_type_id')->orderBy('name')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();

        return view('admin.property-categories.index', compact('categories', 'propertyTypes'));
    }

    public function create()
    {
        $propertyTypes = PropertyType::where('status', true)->orderBy('name')->get();

        return view('admin.property-categories.create', compact('propertyTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_type_id' => ['required', 'integer', 'exists:property_types,id'],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:property_categories,slug'],
            'status' => ['required', 'boolean'],
        ]);

        PropertyCategory::create($data);

        return redirect()->route('admin.property-categories.index')->with('success', 'Property category added successfully.');
    }

    public function edit(PropertyCategory $propertyCategory)
    {
        $propertyTypes = PropertyType::where('status', true)->orderBy('name')->get();

        return view('admin.property-categories.edit', compact('propertyCategory', 'propertyTypes'));
    }

    public function update(Request $request, PropertyCategory $propertyCategory)
    {
        $data = $request->validate([
            'property_type_id' => ['required', 'integer', 'exists:property_types,id'],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:property_categories,slug,' . $propertyCategory->id],
            'status' => ['required', 'boolean'],
        ]);

        $propertyCategory->update($data);

        return redirect()->route('admin.property-categories.index')->with('success', 'Property category updated successfully.');
    }

    public function destroy(PropertyCategory $propertyCategory)
    {
        $propertyCategory->delete();

        return redirect()->route('admin.property-categories.index')->with('success', 'Property category deleted successfully.');
    }

    public function toggleStatus(PropertyCategory $propertyCategory)
    {
        $propertyCategory->update(['status' => !$propertyCategory->status]);

        return redirect()->route('admin.property-categories.index')->with('success', 'Property category status updated successfully.');
    }
}

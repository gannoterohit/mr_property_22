<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use App\Models\Room;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function index()
    {
        $types = PropertyType::orderBy('name')->get();

        return view('admin.property-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.property-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:property_types,slug'],
            'status' => ['required', 'boolean'],
        ]);

        PropertyType::create($data);

        return redirect()->route('admin.property-types.index')->with('success', 'Property type added successfully.');
    }

    public function edit(PropertyType $propertyType)
    {
        return view('admin.property-types.edit', compact('propertyType'));
    }

    public function update(Request $request, PropertyType $propertyType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:property_types,slug,' . $propertyType->id],
            'status' => ['required', 'boolean'],
        ]);

        $propertyType->update($data);

        return redirect()->route('admin.property-types.index')->with('success', 'Property type updated successfully.');
    }

    public function destroy(PropertyType $propertyType)
    {
        if ($propertyType->categories()->exists() || Room::where('property_type_id', $propertyType->id)->exists()) {
            return redirect()->back()->with('error', 'This property type is in use. Deactivate it instead of deleting it.');
        }

        $propertyType->delete();

        return redirect()->route('admin.property-types.index')->with('success', 'Property type deleted successfully.');
    }

    public function toggleStatus(PropertyType $propertyType)
    {
        $propertyType->update(['status' => !$propertyType->status]);

        return redirect()->route('admin.property-types.index')->with('success', 'Property type status updated successfully.');
    }
}

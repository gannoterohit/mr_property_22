<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiPropertyCatalogController extends Controller
{
    public function propertyTypes(Request $request)
    {
        $types = PropertyType::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => $types,
        ]);
    }

    public function propertyCategories(Request $request)
    {
        $request->validate([
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
        ]);

        $categories = PropertyCategory::query()
            ->where('property_type_id', $request->property_type_id)
            ->publicSelectable()
            ->orderBy('name')
            ->get(['id', 'property_type_id', 'name', 'slug']);

        return response()->json([
            'data' => $categories,
        ]);
    }
}

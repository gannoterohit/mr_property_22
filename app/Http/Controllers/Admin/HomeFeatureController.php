<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFeature;
use Illuminate\Http\Request;

class HomeFeatureController extends Controller
{
    public function index(Request $request)
    {
        $features = HomeFeature::query()
            ->when($request->filled('search'), fn ($query) => $query
                ->where('title', 'like', '%'.$request->search.'%'))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.home-features.index', compact('features'));
    }

    public function create()
    {
        $feature = new HomeFeature(['icon' => 'fa-circle-check', 'is_active' => true]);
        return view('admin.home-features.form', compact('feature'));
    }

    public function store(Request $request)
    {
        HomeFeature::create($this->validated($request));
        return redirect()->route('admin.home-features.index')->with('success', 'Why Choose Us item created.');
    }

    public function edit(HomeFeature $homeFeature)
    {
        $feature = $homeFeature;
        return view('admin.home-features.form', compact('feature'));
    }

    public function update(Request $request, HomeFeature $homeFeature)
    {
        $homeFeature->update($this->validated($request));
        return redirect()->route('admin.home-features.index')->with('success', 'Why Choose Us item updated.');
    }

    public function destroy(HomeFeature $homeFeature)
    {
        $homeFeature->delete();
        return back()->with('success', 'Why Choose Us item deleted.');
    }

    public function toggleStatus(HomeFeature $homeFeature)
    {
        $homeFeature->update(['is_active' => !$homeFeature->is_active]);
        return back()->with('success', $homeFeature->is_active ? 'Item activated.' : 'Item deactivated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['icon'] = $data['icon'] ?: 'fa-circle-check';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

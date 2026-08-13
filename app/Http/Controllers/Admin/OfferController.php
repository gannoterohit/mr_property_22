<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $allOffers = Offer::latest()->get();
        $placementCounts = $allOffers
            ->filter(fn (Offer $offer) => $offer->is_active && (!$offer->end_date || $offer->end_date->endOfDay()->gte(now())))
            ->groupBy('placement')
            ->map->count();

        $offers = Offer::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim($request->string('search'));
                $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('discount_text', 'like', "%{$term}%"));
            })
            ->when($request->filled('placement'), fn ($query) => $query->where('placement', $request->string('placement')))
            ->when($request->filled('audience'), fn ($query) => $query->where('target_audience', $request->string('audience')))
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.offers.index', compact('offers', 'placementCounts'));
    }

    public function create()
    {
        return view('admin.offers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_text' => 'nullable|string|max:50',
            'target_audience' => 'required|in:user,owner,both',
            'banner_color' => 'required|string|max:7',
            'placement' => 'required|string|in:' . implode(',', array_keys(Offer::PLACEMENTS)),
            'type' => 'required|string|in:text_only,image_only,both',
            'link_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if (in_array($validated['type'], ['image_only', 'both'], true) && !$request->hasFile('image')) {
            return back()->withInput()->withErrors(['image' => 'Please upload an image for the selected offer type.']);
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = \App\Services\ImageOptimizer::optimize($request->file('image'), 'offer_image');
        }

        $validated['is_active'] = $request->has('is_active');

        Offer::create($validated);

        return redirect()->route('admin.offers.index')->with('success', 'Offer created successfully!');
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.edit', compact('offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_text' => 'nullable|string|max:50',
            'target_audience' => 'required|in:user,owner,both',
            'banner_color' => 'required|string|max:7',
            'placement' => 'required|string|in:' . implode(',', array_keys(Offer::PLACEMENTS)),
            'type' => 'required|string|in:text_only,image_only,both',
            'link_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if (in_array($validated['type'], ['image_only', 'both'], true) && !$request->hasFile('image') && !$offer->image_path) {
            return back()->withInput()->withErrors(['image' => 'Please upload an image for the selected offer type.']);
        }

        if ($request->hasFile('image')) {
            if ($offer->image_path) {
                Storage::disk('public')->delete($offer->image_path);
            }
            $validated['image_path'] = \App\Services\ImageOptimizer::optimize($request->file('image'), 'offer_image');
        }

        $validated['is_active'] = $request->has('is_active');

        $offer->update($validated);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully!');
    }

    public function destroy(Offer $offer)
    {
        if ($offer->image_path) {
            Storage::disk('public')->delete($offer->image_path);
        }
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully!');
    }

    public function toggleActive(Offer $offer)
    {
        $offer->update(['is_active' => !$offer->is_active]);
        return back()->with('success', 'Offer status updated!');
    }

    public function updateDisplaySettings(Request $request)
    {
        $data = $request->validate([
            'popup_delay' => ['required', 'integer', 'min:0', 'max:300'],
        ]);
        Setting::set('popup_delay', $data['popup_delay']);
        return back()->with('success', 'Offer display settings updated.');
    }
}

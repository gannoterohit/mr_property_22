<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponUsage;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Offer::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->string('search'));
                $q->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                match ($request->input('status')) {
                    'active'    => $q->where('is_active', true),
                    'inactive'  => $q->where('is_active', false),
                    default     => null,
                };
            })
            ->withCount('usages')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalSavings = CouponUsage::sum('discount_amount');
        $totalUsages  = CouponUsage::count();

        return view('admin.offers.index', compact('coupons', 'totalSavings', 'totalUsages'));
    }

    public function create()
    {
        return view('admin.offers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'code'            => 'required|string|max:30|unique:offers,code|regex:/^[A-Z0-9_]+$/i',
            'discount_type'   => 'required|in:percentage,flat',
            'discount_value'  => 'required|numeric|min:1|max:100000',
            'max_discount_cap'=> 'nullable|numeric|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_uses'        => 'nullable|integer|min:1',
            'per_user_limit'  => 'required|integer|min:1|max:10',
            'applicable_for'  => 'required|in:all,owner_plans,user_plans,broker_plans,unlocks',
            'target_audience' => 'required|in:all,user,owner,broker,both',
            'show_as_banner'  => 'nullable|boolean',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['show_as_banner'] = $request->boolean('show_as_banner');

        Offer::create($validated);

        return redirect()->route('admin.offers.index')->with('success', 'Coupon "' . $validated['code'] . '" created successfully!');
    }

    public function edit(Offer $offer)
    {
        $usageStats = $offer->usages()->selectRaw('COUNT(*) as total_uses, SUM(discount_amount) as total_savings')->first();
        return view('admin.offers.edit', compact('offer', 'usageStats'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'code'            => 'required|string|max:30|unique:offers,code,' . $offer->id . '|regex:/^[A-Z0-9_]+$/i',
            'discount_type'   => 'required|in:percentage,flat',
            'discount_value'  => 'required|numeric|min:1|max:100000',
            'max_discount_cap'=> 'nullable|numeric|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_uses'        => 'nullable|integer|min:1',
            'per_user_limit'  => 'required|integer|min:1|max:10',
            'applicable_for'  => 'required|in:all,owner_plans,user_plans,broker_plans,unlocks',
            'target_audience' => 'required|in:all,user,owner,broker,both',
            'show_as_banner'  => 'nullable|boolean',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_as_banner'] = $request->boolean('show_as_banner');

        $offer->update($validated);

        return redirect()->route('admin.offers.index')->with('success', 'Coupon updated successfully!');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'Coupon deleted.');
    }

    public function toggleActive(Offer $offer)
    {
        $offer->update(['is_active' => !$offer->is_active]);
        return back()->with('success', 'Coupon status updated!');
    }
}

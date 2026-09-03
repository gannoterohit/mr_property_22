<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokerPlan;
use Illuminate\Http\Request;

class AdminBrokerPlanController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        $plans = BrokerPlan::orderBy('sort_order')->paginate(20);

        return view('admin.broker-plans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        return view('admin.broker-plans.create');
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:broker_plans,slug',
            'type' => 'required|in:monthly,yearly,per_listing',
            'price' => 'required|numeric|min:0',
            'max_listings' => 'nullable|integer|min:-1',
            'duration_days' => 'nullable|integer|min:0',
            'is_featured_included' => 'nullable|boolean',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_featured_included'] = $request->has('is_featured_included');
        $data['is_active'] = true;

        BrokerPlan::create($data);

        return redirect()->route('admin.broker-plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Request $request, BrokerPlan $brokerPlan)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        return view('admin.broker-plans.edit', compact('brokerPlan'));
    }

    public function update(Request $request, BrokerPlan $brokerPlan)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:broker_plans,slug,' . $brokerPlan->id,
            'type' => 'required|in:monthly,yearly,per_listing',
            'price' => 'required|numeric|min:0',
            'max_listings' => 'nullable|integer|min:-1',
            'duration_days' => 'nullable|integer|min:0',
            'is_featured_included' => 'nullable|boolean',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_featured_included'] = $request->has('is_featured_included');
        $data['is_active'] = $request->has('is_active');

        $brokerPlan->update($data);

        return redirect()->route('admin.broker-plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Request $request, BrokerPlan $brokerPlan)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        if ($brokerPlan->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $brokerPlan->delete();

        return redirect()->route('admin.broker-plans.index')->with('success', 'Plan deleted successfully.');
    }

    public function toggleActive(Request $request, BrokerPlan $brokerPlan)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.plans.manage'), 403);

        $brokerPlan->update(['is_active' => !$brokerPlan->is_active]);

        return back()->with('success', 'Plan status updated.');
    }
}

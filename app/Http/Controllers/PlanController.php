<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        // For admin, show all plans
        if (Auth::check() && Auth::user()->role === 'admin') {
            $plans = Plan::all();
            $contactPlans = Plan::where('type', 'user')->where(fn ($q) => $q->where('contacts_limit', '>', 0)->orWhere('contacts_limit', -1))->get();
            $listingPlans = Plan::where('type', 'owner')->where(fn ($q) => $q->where('listing_limit', '>', 0)->orWhere('listing_limit', -1))->get();
            return view('admin.plans.index', compact('plans', 'contactPlans', 'listingPlans'));
        }
        
        // Show contact subscription plans ONLY to users (ACTIVE ONLY)
        if (Auth::check() && Auth::user()->role === 'user') {
            $contactPlans = Plan::where('type', 'user')
                ->where(fn ($q) => $q->where('contacts_limit', '>', 0)->orWhere('contacts_limit', -1))
                ->where('is_active', true)
                ->get();
            $activeSubscription = Auth::user()->subscriptions()->where('status', 'active')->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($q) => $q->where('type', 'user'))->with('plan')->latest()->first();
            return view('account.plans', ['plans' => $contactPlans, 'activeSubscription' => $activeSubscription]);
        }
        
        // Show room listing plans ONLY to owners (ACTIVE ONLY)
        if (Auth::check() && Auth::user()->role === 'owner') {
            $listingPlans = Plan::where('type', 'owner')
                ->where(fn ($q) => $q->where('listing_limit', '>', 0)->orWhere('listing_limit', -1))
                ->where('is_active', true)
                ->get();
            $activeSubscription = Auth::user()->subscriptions()->where('status', 'active')->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($q) => $q->where('type', 'owner'))->with('plan')->latest()->first();
            return view('account.plans', ['plans' => $listingPlans, 'activeSubscription' => $activeSubscription]);
        }
        
        // Plans are role-specific, so guests must sign in first.
        return redirect()->route('login');
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'listing_limit' => 'nullable|integer|min:-1',
            'contacts_limit' => 'nullable|integer|min:-1',
            'type' => 'required|in:owner,user',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string'
        ]);

        Plan::create($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully!');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'listing_limit' => 'nullable|integer|min:-1',
            'contacts_limit' => 'nullable|integer|min:-1',
            'type' => 'required|in:owner,user',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string'
        ]);

        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully!');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully!');
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.plans.index')->with('success', "Plan {$status} successfully!");
    }
}

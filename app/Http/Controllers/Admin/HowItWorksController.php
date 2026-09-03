<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HowItWorksItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HowItWorksController extends Controller
{
    public function index()
    {
        $items = HowItWorksItem::orderBy('group')->orderBy('sort_order')->orderBy('id')->get()->groupBy('group');
        $groups = HowItWorksItem::GROUPS;

        return view('admin.how-it-works.index', compact('items', 'groups'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'hiw_hero_eyebrow' => ['required', 'string', 'max:120'],
            'hiw_hero_title' => ['required', 'string', 'max:160'],
            'hiw_hero_highlight' => ['nullable', 'string', 'max:160'],
            'hiw_hero_description' => ['required', 'string', 'max:700'],
            'hiw_primary_button_label' => ['required', 'string', 'max:80'],
            'hiw_secondary_button_label' => ['required', 'string', 'max:80'],
            'hiw_seeker_eyebrow' => ['required', 'string', 'max:120'],
            'hiw_seeker_title' => ['required', 'string', 'max:160'],
            'hiw_seeker_description' => ['required', 'string', 'max:500'],
            'hiw_owner_eyebrow' => ['required', 'string', 'max:120'],
            'hiw_owner_title' => ['required', 'string', 'max:160'],
            'hiw_owner_description' => ['required', 'string', 'max:500'],
            'hiw_owner_button_label' => ['required', 'string', 'max:80'],
            'hiw_safety_title' => ['required', 'string', 'max:160'],
            'hiw_safety_description' => ['required', 'string', 'max:700'],
            'hiw_safety_button_label' => ['required', 'string', 'max:80'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'How It Works page settings updated.');
    }

    public function create()
    {
        $item = new HowItWorksItem(['group' => request('group', 'seeker_step'), 'icon' => 'fa-circle-check', 'is_active' => true]);
        $groups = HowItWorksItem::GROUPS;

        return view('admin.how-it-works.form', compact('item', 'groups'));
    }

    public function store(Request $request)
    {
        HowItWorksItem::create($this->validated($request));

        return redirect()->route('admin.how-it-works.index')->with('success', 'How It Works item created.');
    }

    public function edit(HowItWorksItem $item)
    {
        $groups = HowItWorksItem::GROUPS;

        return view('admin.how-it-works.form', compact('item', 'groups'));
    }

    public function update(Request $request, HowItWorksItem $item)
    {
        $item->update($this->validated($request));

        return redirect()->route('admin.how-it-works.index')->with('success', 'How It Works item updated.');
    }

    public function destroy(HowItWorksItem $item)
    {
        $item->delete();

        return back()->with('success', 'How It Works item deleted.');
    }

    public function toggleStatus(HowItWorksItem $item)
    {
        $item->update(['is_active' => !$item->is_active]);

        return back()->with('success', $item->is_active ? 'Item activated.' : 'Item deactivated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(array_keys(HowItWorksItem::GROUPS))],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:700'],
            'icon' => ['nullable', 'string', 'max:80'],
            'badge' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['icon'] = $data['icon'] ?: 'fa-circle-check';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

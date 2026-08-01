<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomOption;
use Illuminate\Http\Request;

class RoomOptionController extends Controller
{
    private const GROUPS = [
        'room_type' => 'Room Type',
        'furnishing_type' => 'Furnishing',
        'tenant_type' => 'Preferred Tenant',
        'amenity' => 'Amenities',
    ];

    public function index()
    {
        $groups = self::GROUPS;
        $options = RoomOption::orderBy('group')->orderBy('sort_order')->orderBy('label')->get()->groupBy('group');

        return view('admin.room-options.index', compact('options', 'groups'));
    }

    public function create()
    {
        $groups = self::GROUPS;

        return view('admin.room-options.create', compact('groups'));
    }

    public function edit(RoomOption $roomOption)
    {
        $groups = self::GROUPS;

        return view('admin.room-options.edit', compact('roomOption', 'groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group' => 'required|in:room_type,furnishing_type,tenant_type,amenity',
            'key' => 'required|string|max:100|regex:/^[a-z0-9_\-]+$/|unique:room_options,key',
            'label' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        RoomOption::create(array_merge($data, ['is_active' => true]));

        return redirect()->route('admin.room-options.index')->with('success', 'Room option added successfully.');
    }

    public function update(Request $request, RoomOption $roomOption)
    {
        $data = $request->validate([
            'group' => 'required|in:room_type,furnishing_type,tenant_type,amenity',
            'key' => 'required|string|max:100|regex:/^[a-z0-9_\-]+$/|unique:room_options,key,' . $roomOption->id,
            'label' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $roomOption->update($data);

        return redirect()->route('admin.room-options.index')->with('success', 'Room option updated successfully.');
    }

    public function destroy(RoomOption $roomOption)
    {
        $roomOption->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Room option removed successfully.');
    }

    public function toggleStatus(RoomOption $roomOption)
    {
        $roomOption->update(['is_active' => !$roomOption->is_active]);

        $message = $roomOption->is_active ? 'Room option activated successfully.' : 'Room option deactivated successfully.';

        return redirect()->route('admin.room-options.index')->with('success', $message);
    }
}

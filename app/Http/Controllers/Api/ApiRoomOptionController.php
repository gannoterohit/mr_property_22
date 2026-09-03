<?php

namespace App\Http\Controllers\Api;

use App\Models\RoomOption;

class ApiRoomOptionController extends BaseApiController
{
    /**
     * Return every active admin-managed option used by room forms/filters.
     */
    public function index()
    {
        $groups = collect([
            'room_type',
            'furnishing_type',
            'tenant_type',
            'amenity',
        ])->mapWithKeys(fn (string $group) => [
            $group => RoomOption::optionsFor($group)
                ->map(fn ($option) => [
                    'id' => $option->id,
                    'key' => $option->key,
                    'label' => $option->label,
                ])
                ->values(),
        ]);

        return $this->sendSuccess($groups, 'Room options fetched successfully');
    }
}

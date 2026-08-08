<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'property_type_id' => $this->property_type_id,
            'property_category_id' => $this->property_category_id,
            'property_type' => $this->whenLoaded('propertyType', fn () => $this->propertyType ? [
                'id' => $this->propertyType->id,
                'name' => $this->propertyType->name,
                'slug' => $this->propertyType->slug,
            ] : null),
            'property_category' => $this->whenLoaded('propertyCategory', fn () => $this->propertyCategory ? [
                'id' => $this->propertyCategory->id,
                'property_type_id' => $this->propertyCategory->property_type_id,
                'name' => $this->propertyCategory->name,
                'slug' => $this->propertyCategory->slug,
            ] : null),
            'room_type_option_id' => $this->room_type_option_id,
            'furnishing_option_id' => $this->furnishing_option_id,
            'tenant_option_id' => $this->tenant_option_id,
            // Backward-compatible aliases for existing mobile clients.
            'room_type' => $this->room_type_option_id,
            'furnishing_type' => $this->furnishing_option_id,
            'tenant_type' => $this->tenant_option_id,
            'room_type_label' => $this->roomTypeLabel(),
            'furnishing_type_label' => $this->furnishingTypeLabel(),
            'tenant_type_label' => $this->tenantTypeLabel(),
            'amenities' => $this->amenities ?? [],
            'rent' => (float) $this->rent,
            'deposit' => (float) $this->deposit,
            'area_sqft' => $this->area_sqft !== null ? (float) $this->area_sqft : null,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'photo_url' => $this->photo_url,
            'photo_urls' => $this->photo_urls,
            'video_url' => $this->video ? asset('storage/' . $this->video) : $this->video_url,
            'is_featured' => (bool) $this->is_featured,
            'listing_fee_paid' => (bool) $this->listing_fee_paid,
            'status' => $this->status,
            'listing_status' => $this->listing_status,
            'rejection_reason' => $this->rejection_reason,
            'available_from' => $this->available_from,
            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'avatar' => $this->owner->avatar ? asset('storage/' . $this->owner->avatar) : null,
                'is_verified' => (bool) $this->owner->is_verified,
                'verification_status' => $this->owner->verification_status,
            ] : null),
            'landmarks' => $this->landmarks ?? [],
            'listing_type' => $this->listing_type,
            'broker_fee' => (float) ($this->broker_fee ?? 0),
            'created_at' => $this->created_at,
        ];
    }
}

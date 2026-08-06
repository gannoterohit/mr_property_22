<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'single_room' => ['room', 'single-room'],
            'shared_room' => ['room', 'shared-room'],
            'private_room' => ['room', 'single-room'],
            '1bhk' => ['flat', '1bhk'],
            '2bhk' => ['flat', '2bhk'],
            '3bhk' => ['flat', '3bhk'],
            'flat' => ['flat', '4bhk'],
            '1rk' => ['flat', '1rk'],
            'studio_apartment' => ['flat', '1rk'],
            '4bhk_plus' => ['flat', '4bhk'],
            'pg' => ['pg', 'boys-pg'],
            'hostel' => ['pg', 'co-living'],
            'independent_house' => ['house', 'independent-house'],
            'villa' => ['house', 'villa'],
        ];

        $typeIds = DB::table('property_types')
            ->whereIn('slug', array_unique(array_column($mapping, 0)))
            ->pluck('id', 'slug')
            ->all();

        $categoryIds = DB::table('property_categories')
            ->whereIn('slug', array_unique(array_column($mapping, 1)))
            ->pluck('id', 'slug')
            ->all();

        foreach ($mapping as $optionKey => [$typeSlug, $categorySlug]) {
            $roomOptionId = DB::table('room_options')
                ->where('group', 'room_type')
                ->where('key', $optionKey)
                ->value('id');

            if (! $roomOptionId) {
                continue;
            }

            $propertyTypeId = $typeIds[$typeSlug] ?? null;
            $propertyCategoryId = $categoryIds[$categorySlug] ?? null;

            if (! $propertyTypeId || ! $propertyCategoryId) {
                continue;
            }

            DB::table('rooms')
                ->where('room_type_option_id', $roomOptionId)
                ->where(function ($query) {
                    $query->whereNull('property_type_id')
                        ->orWhereNull('property_category_id');
                })
                ->update([
                    'property_type_id' => $propertyTypeId,
                    'property_category_id' => $propertyCategoryId,
                ]);
        }

        $shopTypeId = DB::table('property_types')->where('slug', 'shop')->value('id');
        if ($shopTypeId) {
            $missingShopCategorySlug = 'main-street';
            $exists = DB::table('property_categories')
                ->where('property_type_id', $shopTypeId)
                ->where('slug', $missingShopCategorySlug)
                ->exists();

            if (! $exists) {
                DB::table('property_categories')->insert([
                    'property_type_id' => $shopTypeId,
                    'name' => 'Main Street',
                    'slug' => $missingShopCategorySlug,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // This migration updates legacy room records and adds a supplemental shop category.
        // Reverting the legacy room backfill is intentionally not supported.
    }
};

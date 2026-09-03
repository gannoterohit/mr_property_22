<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
<<<<<<< HEAD
        // Rejection reasons are managed from the admin panel.
=======
        $reasons = [
            'Property photos are unclear, incomplete, or misleading',
            'Property address or location information is incomplete',
            'Rent, deposit, or additional charges are incorrect or unclear',
            'Room type or property details do not match the listing',
            'Required amenities and facilities information is incomplete',
            'Duplicate listing already exists for this property',
            'Owner KYC or contact verification is incomplete',
            'Listing contains prohibited, offensive, or misleading content',
            'Property is unavailable, already rented, or not ready for rent',
            'Listing does not meet ApnaNest quality and safety guidelines',
        ];
        foreach ($reasons as $reason) DB::table('rejection_reasons')->updateOrInsert(
            ['reason' => $reason],
            ['is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
<<<<<<< HEAD
        // No records are created by this migration.
=======
        DB::table('rejection_reasons')->whereIn('reason', [
            'Property photos are unclear, incomplete, or misleading','Property address or location information is incomplete','Rent, deposit, or additional charges are incorrect or unclear','Room type or property details do not match the listing','Required amenities and facilities information is incomplete','Duplicate listing already exists for this property','Owner KYC or contact verification is incomplete','Listing contains prohibited, offensive, or misleading content','Property is unavailable, already rented, or not ready for rent','Listing does not meet ApnaNest quality and safety guidelines',
        ])->delete();
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};

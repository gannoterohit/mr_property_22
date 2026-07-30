<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Models\Enquiry;
use App\Models\SearchLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing related data to avoid duplicates if re-run
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payments')->truncate();
        DB::table('bookings')->truncate();
        DB::table('enquiries')->truncate();
        DB::table('search_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $rooms = Room::all();
        $users = User::where('role', 'user')->get();
        $owners = User::where('role', 'owner')->get();

        if ($rooms->isEmpty() || $users->isEmpty()) {
            $this->command->info('Need rooms and users to seed activity data.');
            return;
        }

        $this->command->info('Seeding income and activity data...');

        // 1. Listing Fee Payments (For each room)
        $listingFee = Setting::get('listing_fee', 199);
        foreach ($rooms as $room) {
            $date = Carbon::now()->subDays(rand(1, 90));
            Payment::create([
                'user_id' => $room->user_id,
                'type' => 'listing',
                'amount' => $listingFee,
                'gateway' => 'razorpay',
                'transaction_id' => 'pay_' . Str::random(14),
                'reference_id' => $room->id,
                'status' => 'completed',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            
            // Mark room as paid
            $room->update(['listing_fee_paid' => true, 'listing_status' => 'approved']);
        }

        // 2. Featured Room Payments
        $featuredFee = Setting::get('featured_fee', 99);
        $featuredRooms = $rooms->random(min(15, $rooms->count()));
        foreach ($featuredRooms as $room) {
            $date = Carbon::now()->subDays(rand(1, 60));
            Payment::create([
                'user_id' => $room->user_id,
                'type' => 'featured',
                'amount' => $featuredFee,
                'gateway' => 'razorpay',
                'transaction_id' => 'pay_' . Str::random(14),
                'reference_id' => $room->id,
                'status' => 'completed',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            $room->update(['is_featured' => true]);
        }

        // 3. Contact Unlocks (Enquiries)
        $unlockFee = Setting::get('unlock_fee', 49);
        $enquiryPairs = collect();
        foreach ($users as $user) {
            foreach ($rooms as $room) {
                $enquiryPairs->push([$user, $room]);
            }
        }

        foreach ($enquiryPairs->shuffle()->take(min(60, $enquiryPairs->count())) as [$user, $room]) {
            $date = Carbon::now()->subDays(rand(1, 90));

            $payment = Payment::create([
                'user_id' => $user->id,
                'type' => 'unlock',
                'amount' => $unlockFee,
                'gateway' => 'razorpay',
                'transaction_id' => 'pay_' . Str::random(14),
                'reference_id' => $room->id,
                'status' => 'completed',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            Enquiry::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'payment_id' => $payment->id,
                'unlocked' => true,
                'unlocked_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // 6. Subscriptions
        $plans = \App\Models\Plan::all();
        if ($plans->isNotEmpty()) {
            for ($i = 0; $i < 15; $i++) {
                $user = (rand(0, 1)) ? $users->random() : $owners->random();
                $plan = $plans->random();
                $date = Carbon::now()->subDays(rand(1, 90));

                $subscription = \App\Models\Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'start_date' => $date,
                    'end_date' => (clone $date)->addDays($plan->duration_days),
                    'status' => 'active',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                Payment::create([
                    'user_id' => $user->id,
                    'type' => 'subscription',
                    'amount' => $plan->price,
                    'gateway' => 'razorpay',
                    'transaction_id' => 'pay_' . Str::random(14),
                    'reference_id' => $subscription->id,
                    'status' => 'completed',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        // 7. Search Logs for Analytics
        $cities = ['Bhopal', 'Indore', 'Bangalore', 'Mumbai', 'Delhi', 'Pune'];
        for ($i = 0; $i < 150; $i++) {
            $city = $cities[array_rand($cities)];
            $user = (rand(0, 1)) ? $users->random() : null;
            $date = Carbon::now()->subDays(rand(0, 90));

            SearchLog::create([
                'city' => $city,
                'search_term' => $city,
                'filters' => [
                    'min_rent' => rand(2000, 5000),
                    'max_rent' => rand(10000, 30000),
                    'is_auto_detected' => (rand(0, 10) > 7)
                ],
                'user_id' => $user ? $user->id : null,
                'ip_address' => '127.0.0.' . rand(1, 255),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->command->info('Dummy income and activity data seeded successfully!');
    }
}

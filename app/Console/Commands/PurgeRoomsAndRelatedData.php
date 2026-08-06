<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeRoomsAndRelatedData extends Command
{
    protected $signature = 'rooms:purge {
            --all : Delete all rooms
        } {
            --before= : Delete rooms created before date (YYYY-MM-DD)
        } {
            --dry-run : Show what would be deleted without deleting anything
        }';

    protected $description = 'Remove rooms and related payments/unlocks/enquiries/bookings/wishlists for selected rooms.';

    public function handle(): int
    {
        $all = $this->option('all');
        $before = $this->option('before');
        $dryRun = $this->option('dry-run');

        if (! $all && ! $before) {
            $this->error('Please specify --all or --before=YYYY-MM-DD.');

            return 1;
        }

        $roomQuery = Room::query();

        if ($before) {
            try {
                $cutoff = Carbon::parse($before)->endOfDay();
                $roomQuery->where('created_at', '<', $cutoff);
            } catch (\Throwable $exception) {
                $this->error('Invalid --before date format. Use YYYY-MM-DD.');

                return 1;
            }
        }

        $roomCount = $roomQuery->count();

        if ($roomCount === 0) {
            $this->info('No rooms matched the cleanup criteria.');

            return 0;
        }

        $roomIds = $roomQuery->pluck('id')->toArray();

        $listingPaymentIds = Room::whereIn('id', $roomIds)
            ->whereNotNull('listing_payment_id')
            ->pluck('listing_payment_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $enquiryPaymentIds = Enquiry::whereIn('room_id', $roomIds)
            ->whereNotNull('payment_id')
            ->pluck('payment_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $bookingPaymentIds = DB::table('bookings')
            ->whereIn('room_id', $roomIds)
            ->whereNotNull('payment_id')
            ->pluck('payment_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $referencePaymentIds = Payment::whereIn('reference_id', $roomIds)
            ->pluck('id')
            ->unique()
            ->values()
            ->toArray();

        $paymentIds = collect(array_merge($listingPaymentIds, $enquiryPaymentIds, $bookingPaymentIds, $referencePaymentIds))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $analyticsEventCount = AnalyticsEvent::whereIn('room_id', $roomIds)->count();

        $this->table(
            ['Item', 'Count'],
            [
                ['Rooms to delete', $roomCount],
                ['Payments to delete', count($paymentIds)],
                ['Unlock / enquiry payments', count($enquiryPaymentIds)],
                ['Booking payments', count($bookingPaymentIds)],
                ['Listing payments', count($listingPaymentIds)],
                ['Payments referencing room IDs', count($referencePaymentIds)],
                ['Analytics events referencing rooms', $analyticsEventCount],
            ]
        );

        if ($dryRun) {
            $this->info('Dry run complete. No records were deleted.');

            return 0;
        }

        if (! $this->confirm('This will permanently delete the selected rooms and related data. Continue?')) {
            $this->info('No changes were made.');

            return 0;
        }

        DB::transaction(function () use ($roomIds, $paymentIds) {
            if (! empty($paymentIds)) {
                Payment::whereIn('id', $paymentIds)->delete();
            }

            AnalyticsEvent::whereIn('room_id', $roomIds)->delete();

            Room::whereIn('id', $roomIds)->chunkById(100, function ($rooms) {
                foreach ($rooms as $room) {
                    $this->deleteRoomMedia($room);
                    $room->delete();
                }
            });
        });

        $this->info('Deleted '.$roomCount.' rooms and related records.');

        return 0;
    }

    private function deleteRoomMedia(Room $room): void
    {
        $paths = [];

        if ($room->photo) {
            $paths[] = $room->photo;
        }

        if ($room->video) {
            $paths[] = $room->video;
        }

        if (is_array($room->photos)) {
            $paths = array_merge($paths, $room->photos);
        }

        if (empty($paths)) {
            return;
        }

        $paths = array_map(fn ($path) => ltrim(str_replace('\\', '/', $path), '/'), $paths);

        Storage::disk('public')->delete($paths);
    }
}

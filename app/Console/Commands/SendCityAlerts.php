<?php

namespace App\Console\Commands;

use App\Models\CityAlert;
use App\Models\Room;
use App\Models\UserNotification;
use Illuminate\Console\Command;

class SendCityAlerts extends Command
{
    protected $signature = 'alerts:send-city
                            {--hours=24 : Only consider rooms created in the last N hours}
                            {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Send city alerts to users when new rooms are listed in their subscribed cities';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $this->info("Processing city alerts (last {$hours} hours)..." . ($dryRun ? ' [DRY RUN]' : ''));

        $newRooms = Room::publicVisible()
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereNotNull('city')
            ->get();

        if ($newRooms->isEmpty()) {
            $this->info('No new rooms in the time window.');
            return self::SUCCESS;
        }

        $alertsByCity = CityAlert::with('user:id,name,email,fcm_token,web_push_token')
            ->get()
            ->groupBy(fn ($alert) => strtolower(trim($alert->city)));

        $this->info("Found {$newRooms->count()} new rooms across " . $newRooms->pluck('city')->unique()->count() . " cities.");
        $this->info("Found " . CityAlert::count() . " city alert subscriptions.");

        $totalSent = 0;

        foreach ($newRooms->groupBy(fn ($r) => strtolower(trim($r->city))) as $cityKey => $rooms) {
            $alerts = $alertsByCity->get($cityKey, collect());
            if ($alerts->isEmpty()) {
                continue;
            }

            $this->line("→ {$rooms->first()->city}: " . $rooms->count() . " new room(s), " . $alerts->count() . " subscriber(s)");

            foreach ($alerts as $alert) {
                if ($alert->user->id === $rooms->first()->user_id) {
                    continue;
                }

                $roomList = $rooms->take(3)->map(fn ($r) => $r->title)->implode(', ');
                $count = $rooms->count();

                $message = $count === 1
                    ? "New room in {$rooms->first()->city}: {$rooms->first()->title}"
                    : "{$count} new rooms in {$rooms->first()->city}: {$roomList}";

                $this->line("  → User: {$alert->user->name} (ID: {$alert->user->id}) - {$message}");

                if ($dryRun) {
                    continue;
                }

                $notification = UserNotification::send(
                    $alert->user->id,
                    'city_alert',
                    'New rooms in ' . $rooms->first()->city,
                    $message,
                    route('rooms.index', ['city' => $rooms->first()->city]),
                    'fa-bell',
                    $rooms->first()->photo
                );

                if (!empty($alert->user->fcm_token)) {
                    try {
                        app(\App\Services\NotificationService::class)->sendFcm(
                            $alert->user->fcm_token,
                            'New rooms in ' . $rooms->first()->city,
                            $message,
                            ['type' => 'city_alert', 'city' => $rooms->first()->city]
                        );
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }

                $totalSent++;
            }
        }

        $this->info($dryRun ? "Dry run complete. Would have sent " . $totalSent . " alerts." : "Sent {$totalSent} city alerts.");
        return self::SUCCESS;
    }
}

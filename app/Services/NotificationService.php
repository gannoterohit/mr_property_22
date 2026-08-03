<?php

namespace App\Services;

use App\Mail\BrandedMessageMail;
use App\Models\AdminNotification;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify Tenant (User) + Admin when room contact details are unlocked.
     */
    public static function notifyContactUnlocked(User $user, Room $room): void
    {
        try {
            $room->loadMissing('owner');

            // 1. Bell icon notification to Tenant
            try {
                UserNotification::send(
                    $user->id,
                    'contact_unlock',
                    "Contact Unlocked: {$room->title}",
                    "You've unlocked the owner contact for '{$room->title}'. Visit the room page to view the number.",
                    route('rooms.show', $room->slug),
                    'fa-key'
                );
            } catch (\Exception $e) {
                Log::warning("Bell notification for unlock failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification to Tenant (Mobile + Web)
            FirebaseService::sendToUser(
                $user,
                "Contact Unlocked 🔑",
                "You unlocked '{$room->title}'. Tap to view owner details.",
                ['type' => 'contact_unlock', 'room_slug' => $room->slug ?? ''],
                route('rooms.show', $room->slug)
            );

            // 3. Send Email Confirmation to Tenant (without exposing phone number — drives return traffic)
            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->send(new BrandedMessageMail(
                        "Contact Details Unlocked: {$room->title}",
                        "Contact Unlocked Successfully",
                        "You have successfully unlocked the contact details for '{$room->title}'. Please click below to view the owner's phone number and details on ApnaNest.",
                        "Contact Unlocked",
                        "View Owner Details on Website",
                        route('rooms.show', $room->slug),
                        [
                            'Room Title' => $room->title,
                            'City'       => $room->city ?? 'N/A',
                            'Address'    => $room->address ?? 'N/A',
                            'Status'     => 'Unlocked (Active)',
                        ],
                        'primary',
                        'Note: You can view all your unlocked contacts anytime by visiting your ApnaNest account.'
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning("Unlock confirmation email to tenant failed: " . $mailEx->getMessage());
                }
            }

            // 4. Admin Panel Notification
            try {
                $owner = $room->owner;
                AdminNotification::send(
                    'lead_unlock',
                    'Contact Details Unlocked',
                    "Tenant {$user->name} unlocked contact for room '{$room->title}' (Owner: " . ($owner->name ?? 'N/A') . ")",
                    route('admin.all-rooms', ['search' => $room->title]),
                    'fa-key'
                );
            } catch (\Exception $adminEx) {
                Log::warning("Admin notification for unlock failed: " . $adminEx->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("NotificationService notifyContactUnlocked error: " . $e->getMessage());
        }
    }

    /**
     * Notify User/Owner via bell + Firebase push + email when payment succeeds.
     */
    public static function notifyPaymentSuccess(User $user, Payment $payment, ?Room $room = null): void
    {
        try {
            $paymentLabel = ucfirst($payment->type);
            $amountLabel  = '₹' . number_format($payment->amount, 2);

            // 1. Bell icon notification
            try {
                UserNotification::send(
                    $user->id,
                    'payment_success',
                    "Payment Successful: {$amountLabel}",
                    "Your {$paymentLabel} payment of {$amountLabel} was received successfully.",
                    null,
                    'fa-credit-card'
                );
            } catch (\Exception $e) {
                Log::warning("Bell notification for payment failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $user,
                "Payment Successful ✅",
                "{$amountLabel} received for {$paymentLabel}. Thank you!",
                ['type' => 'payment_success', 'amount' => (string) $payment->amount]
            );

            // 3. Email Receipt
            if ($user && $user->email) {
                try {
                    $details = [
                        'Transaction ID' => $payment->transaction_id ?: $payment->gateway_order_id ?: "PAY-{$payment->id}",
                        'Payment Type'   => $paymentLabel,
                        'Amount Paid'    => $amountLabel,
                        'Payment Mode'   => ucfirst($payment->gateway ?? 'Online'),
                        'Date'           => now()->format('d M Y, h:i A'),
                    ];
                    if ($room) {
                        $details['Related Room'] = $room->title;
                    }
                    Mail::to($user->email)->send(new BrandedMessageMail(
                        "Payment Receipt: {$amountLabel} - ApnaNest",
                        "Payment Received Successfully",
                        "Thank you for your payment. We have processed your transaction successfully. Below is your payment receipt.",
                        "Payment Receipt",
                        "View Account",
                        route('home'),
                        $details,
                        'success'
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning("Payment receipt email failed: " . $mailEx->getMessage());
                }
            }

            // 4. Admin Panel Notification
            try {
                AdminNotification::send(
                    'payment_received',
                    'New Payment Received',
                    "Payment of {$amountLabel} received from {$user->name} ({$user->role}) for {$paymentLabel}",
                    route('admin.payments.index'),
                    'fa-credit-card'
                );
            } catch (\Exception $adminEx) {
                Log::warning("Admin notification for payment failed: " . $adminEx->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("NotificationService notifyPaymentSuccess error: " . $e->getMessage());
        }
    }

    /**
     * Notify Owner when their room listing is approved.
     */
    public static function notifyRoomApproved(Room $room): void
    {
        try {
            $owner = $room->owner ?? User::find($room->user_id);
            if (!$owner) return;

            // Bell notification
            UserNotification::send(
                $owner->id,
                'room_approved',
                "Room Approved: {$room->title}",
                "Your room listing '{$room->title}' has been approved and is now live on ApnaNest!",
                route('rooms.show', $room->slug),
                'fa-check-circle'
            );

            // Firebase Push
            FirebaseService::sendToUser(
                $owner,
                "Room Approved ✅",
                "'{$room->title}' is now live! Tenants can find it.",
                ['type' => 'room_approved', 'room_slug' => $room->slug ?? ''],
                route('rooms.show', $room->slug)
            );

        } catch (\Exception $e) {
            Log::warning("Bell/Firebase notification for room approved failed: " . $e->getMessage());
        }
    }

    /**
     * Notify Owner when their room listing is rejected.
     */
    public static function notifyRoomRejected(Room $room, string $reasons = ''): void
    {
        try {
            $owner = $room->owner ?? User::find($room->user_id);
            if (!$owner) return;

            $msg = "Your room listing '{$room->title}' was not approved." . ($reasons ? " Reason: {$reasons}" : ' Please review and resubmit.');

            // Bell notification
            UserNotification::send(
                $owner->id,
                'room_rejected',
                "Room Rejected: {$room->title}",
                $msg,
                route('rooms.edit', $room->slug),
                'fa-times-circle'
            );

            // Firebase Push
            FirebaseService::sendToUser(
                $owner,
                "Room Needs Revision ⚠️",
                "'{$room->title}' was not approved. Tap to review.",
                ['type' => 'room_rejected', 'room_slug' => $room->slug ?? ''],
                route('rooms.edit', $room->slug)
            );

        } catch (\Exception $e) {
            Log::warning("Bell/Firebase notification for room rejected failed: " . $e->getMessage());
        }
    }

    /**
     * Notify User when their complaint ticket is updated by admin.
     */
    public static function notifyComplaintUpdated(int $userId, string $ticketNumber, string $status, string $complaintRoute): void
    {
        try {
            $statusLabel = ucfirst(str_replace('_', ' ', $status));

            // Bell notification
            UserNotification::send(
                $userId,
                'complaint_update',
                "Complaint Update: #{$ticketNumber}",
                "Your support ticket #{$ticketNumber} status has been updated to: {$statusLabel}.",
                $complaintRoute,
                'fa-headset'
            );

            // Firebase Push
            $user = User::find($userId);
            if ($user) {
                FirebaseService::sendToUser(
                    $user,
                    "Support Ticket Updated 🎧",
                    "Ticket #{$ticketNumber} is now: {$statusLabel}. Tap to view.",
                    ['type' => 'complaint_update', 'ticket' => $ticketNumber],
                    $complaintRoute
                );
            }

        } catch (\Exception $e) {
            Log::warning("Bell/Firebase notification for complaint update failed: " . $e->getMessage());
        }
    }
}

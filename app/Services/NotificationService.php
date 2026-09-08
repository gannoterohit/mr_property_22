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

            // 5. Send Instant Lead Email Alert to Property Host (Owner / Broker)
            $owner = $room->owner;
            if ($owner && $owner->email) {
                try {
                    $ownerRoute = $owner->role === 'broker' ? route('agent.enquiries') : route('owner.enquiries');

                    // Bell Notification to Owner
                    UserNotification::send(
                        $owner->id,
                        'lead_received',
                        "New Lead: {$user->name} unlocked '{$room->title}'",
                        "Tenant {$user->name} (Phone: " . ($user->phone ?? 'N/A') . ") has unlocked contact details for your listing '{$room->title}'.",
                        $ownerRoute,
                        'fa-user-clock'
                    );

                    // Firebase Push to Owner
                    FirebaseService::sendToUser(
                        $owner,
                        "New Lead Received ⚡",
                        "{$user->name} unlocked contact details for '{$room->title}'. Tap to view lead details.",
                        ['type' => 'lead_received', 'room_id' => (string) $room->id],
                        $ownerRoute
                    );

                    // Email to Owner / Broker
                    Mail::to($owner->email)->send(new BrandedMessageMail(
                        "New Lead Alert: Contact Unlocked for {$room->title} ⚡",
                        "New Lead Interested in Your Property",
                        "A prospective tenant has unlocked contact details for your listing '{$room->title}' on ApnaNest. Please reach out to them promptly!",
                        "New Lead Alert",
                        "View Lead Details",
                        $ownerRoute,
                        [
                            'Tenant Name'  => $user->name,
                            'Tenant Phone' => $user->phone ?? 'N/A',
                            'Tenant Email' => $user->email ?? 'N/A',
                            'Property'     => $room->title,
                            'Unlocked At'  => now()->format('d M Y, h:i A'),
                        ],
                        'success',
                        'Pro tip: Replying or calling leads within 15 minutes increases closing success rates!'
                    ));
                } catch (\Exception $ownerMailEx) {
                    Log::warning("Lead alert email to owner failed: " . $ownerMailEx->getMessage());
                }
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

            $editUrl = $owner->role === 'broker' ? route('agent.rooms.edit', $room->slug) : route('owner.rooms.edit', $room->slug);

            // Bell notification
            UserNotification::send(
                $owner->id,
                'room_rejected',
                "Room Rejected: {$room->title}",
                $msg,
                $editUrl,
                'fa-times-circle'
            );

            // Firebase Push
            FirebaseService::sendToUser(
                $owner,
                "Room Needs Revision ⚠️",
                "'{$room->title}' was not approved. Tap to review.",
                ['type' => 'room_rejected', 'room_slug' => $room->slug ?? ''],
                $editUrl
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

    /**
     * Notify newly registered User/Owner/Broker with a Welcome email and bell notification.
     */
    public static function notifyWelcome(User $user): void
    {
        try {
            $roleLabel = ucfirst($user->role);
            $actionUrl = match ($user->role) {
                'broker' => route('agent.dashboard'),
                'owner'  => route('owner.dashboard'),
                default  => route('home'),
            };

            // 1. Bell notification
            try {
                UserNotification::send(
                    $user->id,
                    'welcome',
                    "Welcome to ApnaNest, {$user->name}! 🎉",
                    "Thank you for registering on ApnaNest. Explore verified properties or list your own spaces easily.",
                    $actionUrl,
                    'fa-user-check'
                );
            } catch (\Exception $e) {
                Log::warning("Welcome bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $user,
                "Welcome to ApnaNest! 🎉",
                "Thank you for joining ApnaNest. Tap to explore verified properties!",
                ['type' => 'welcome'],
                $actionUrl
            );

            // 2. Welcome Email
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new BrandedMessageMail(
                        "Welcome to ApnaNest! 🎉",
                        "Welcome to ApnaNest, {$user->name}",
                        "Thank you for joining ApnaNest — your trusted real estate & property listing platform. We are thrilled to have you onboard as a {$roleLabel}!",
                        "Account Created",
                        "Go to My Dashboard",
                        $actionUrl,
                        [
                            'Account Name' => $user->name,
                            'Email'        => $user->email,
                            'Role'         => $roleLabel,
                            'Status'       => 'Active',
                        ],
                        'primary',
                        'Need help getting started? Contact our support team anytime from your account dashboard.'
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning("Welcome email failed: " . $mailEx->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("NotificationService notifyWelcome error: " . $e->getMessage());
        }
    }

    /**
     * Notify Broker when their account verification status is updated (Approved, Rejected, Suspended).
     */
    public static function notifyBrokerStatusChanged(User $broker, string $status, ?string $reason = null): void
    {
        try {
            if (!$broker->isBroker()) return;

            $statusLabel = ucfirst($status);

            $subject = match ($status) {
                'approved'  => "Broker Verification Approved! 🎉",
                'rejected'  => "Broker Verification Status Update ⚠️",
                'suspended' => "Broker Account Suspended ⚠️",
                default     => "Broker Account Status Updated",
            };

            $heading = match ($status) {
                'approved'  => "Your Broker Account is Verified",
                'rejected'  => "Verification Status Update",
                'suspended' => "Account Suspended",
                default     => "Broker Status Updated: {$statusLabel}",
            };

            $bodyText = match ($status) {
                'approved'  => "Congratulations! Your broker verification and agency profile have been approved by admin. You can now post and manage property listings.",
                'rejected'  => "Your broker verification application could not be approved at this time." . ($reason ? " Reason: {$reason}" : " Please update your agency details and re-submit for review."),
                'suspended' => "Your broker account has been temporarily suspended by admin. Please contact support if you believe this is an error.",
                default     => "Your broker account status has been updated to {$statusLabel}.",
            };

            $actionUrl = match ($status) {
                'approved' => route('agent.dashboard'),
                default    => route('agent.pending'),
            };

            // 1. Bell notification
            try {
                UserNotification::send(
                    $broker->id,
                    'broker_status',
                    $heading,
                    $bodyText,
                    $actionUrl,
                    $status === 'approved' ? 'fa-check-circle' : 'fa-exclamation-circle'
                );
            } catch (\Exception $e) {
                Log::warning("Broker status bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push
            FirebaseService::sendToUser(
                $broker,
                $heading,
                $bodyText,
                ['type' => 'broker_status', 'status' => $status],
                $actionUrl
            );

            // 3. Email Notification
            if ($broker->email) {
                try {
                    $details = [
                        'Broker Name'  => $broker->name,
                        'Agency Name'  => $broker->agency_name ?? 'N/A',
                        'New Status'   => $statusLabel,
                        'Updated Date' => now()->format('d M Y, h:i A'),
                    ];
                    if ($reason) {
                        $details['Reason / Notes'] = $reason;
                    }

                    Mail::to($broker->email)->send(new BrandedMessageMail(
                        $subject,
                        $heading,
                        $bodyText,
                        "Broker Account Update",
                        $status === 'approved' ? "Go to Agent Dashboard" : "View Account Status",
                        $actionUrl,
                        $details,
                        $status === 'approved' ? 'success' : 'danger',
                        $status === 'rejected' ? 'You can update your agency details and license information from your profile.' : null
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning("Broker status email failed: " . $mailEx->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("NotificationService notifyBrokerStatusChanged error: " . $e->getMessage());
        }
    }

    /**
     * Notify subscriber when they join newsletter.
     */
    public static function notifyNewsletterSubscribed(string $email): void
    {
        try {
            if (!$email) return;

            Mail::to($email)->send(new BrandedMessageMail(
                "Welcome to ApnaNest Newsletter! 📩",
                "Thank You for Subscribing!",
                "You have successfully subscribed to the ApnaNest newsletter. You will now receive regular updates on trending properties, real estate market insights, and exclusive offers.",
                "Newsletter Subscription",
                "Explore Properties",
                route('home'),
                [
                    'Subscribed Email' => $email,
                    'Date'             => now()->format('d M Y'),
                ],
                'primary',
                'You can unsubscribe at any time using the link in our emails.'
            ));
        } catch (\Exception $e) {
            Log::warning("Newsletter subscription email failed: " . $e->getMessage());
        }
    }

    /**
     * Notify Broker when their listing credits are low (<= 1 credit remaining).
     */
    public static function notifyLowListingCredits(User $broker, int $remainingCredits): void
    {
        try {
            if (!$broker || !$broker->email) return;

            $actionUrl = route('agent.dashboard');

            // 1. Bell notification
            try {
                UserNotification::send(
                    $broker->id,
                    'low_credits',
                    "Low Listing Credits Warning ⚠️",
                    "You have only {$remainingCredits} listing credit(s) remaining. Top-up credits to continue posting.",
                    $actionUrl,
                    'fa-exclamation-triangle'
                );
            } catch (\Exception $e) {
                Log::warning("Low credit bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $broker,
                "Low Listing Credits ⚠️",
                "Only {$remainingCredits} credit(s) remaining. Tap to top-up!",
                ['type' => 'low_credits', 'credits' => (string) $remainingCredits],
                $actionUrl
            );

            // 2. Email Notification
            Mail::to($broker->email)->send(new BrandedMessageMail(
                "Action Required: Low Property Listing Credits ⚠️",
                "Listing Credits Running Low",
                "Your account has only {$remainingCredits} listing credit(s) remaining. Top-up your credits now to ensure uninterrupted listing submission.",
                "Credit Warning",
                "Buy Listing Credits",
                $actionUrl,
                [
                    'Broker Name'        => $broker->name,
                    'Remaining Credits' => (string)$remainingCredits,
                    'Status'            => 'Low Balance',
                ],
                'warning',
                'Credits can be purchased instantly via your agent dashboard.'
            ));
        } catch (\Exception $e) {
            Log::error("NotificationService notifyLowListingCredits error: " . $e->getMessage());
        }
    }

    /**
     * Notify Broker when a tenant posts an approved rating/review on their profile.
     */
    public static function notifyBrokerReviewReceived(User $broker, $review): void
    {
        try {
            if (!$broker || !$broker->email) return;

            $reviewerName = $review->user->name ?? 'A Tenant';
            $ratingStars  = str_repeat('⭐', (int) ($review->rating ?? 5));
            $actionUrl    = route('agent.dashboard');

            // 1. Bell notification
            try {
                UserNotification::send(
                    $broker->id,
                    'broker_review',
                    "New Review Received ({$ratingStars}) ⭐",
                    "{$reviewerName} rated you {$review->rating}/5 stars on ApnaNest.",
                    $actionUrl,
                    'fa-star'
                );
            } catch (\Exception $e) {
                Log::warning("Broker review bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $broker,
                "New Review Received ⭐",
                "{$reviewerName} rated you {$review->rating}/5 stars on ApnaNest.",
                ['type' => 'broker_review'],
                $actionUrl
            );

            // 2. Email Notification
            Mail::to($broker->email)->send(new BrandedMessageMail(
                "New Tenant Review & Rating Received! {$ratingStars}",
                "You Received a New Review!",
                "A tenant has published a review on your broker profile on ApnaNest. Positive reviews build trust and attract more leads!",
                "New Client Review",
                "View My Profile Reviews",
                $actionUrl,
                [
                    'Reviewer' => $reviewerName,
                    'Rating'   => "{$review->rating} / 5 Stars ({$ratingStars})",
                    'Review'   => $review->comment ?: 'No written comment.',
                    'Date'     => now()->format('d M Y'),
                ],
                'success'
            ));
        } catch (\Exception $e) {
            Log::error("NotificationService notifyBrokerReviewReceived error: " . $e->getMessage());
        }
    }

    /**
     * Notify Broker when a property listing is submitted for admin approval.
     */
    public static function notifyPropertySubmitted(User $broker, Room $room): void
    {
        try {
            if (!$broker || !$broker->email) return;

            $actionUrl = route('agent.dashboard');

            // 1. Bell notification
            try {
                UserNotification::send(
                    $broker->id,
                    'property_submitted',
                    "Property Submitted: {$room->title}",
                    "Your listing '{$room->title}' was submitted and is pending admin approval.",
                    $actionUrl,
                    'fa-file-upload'
                );
            } catch (\Exception $e) {
                Log::warning("Property submitted bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $broker,
                "Property Submitted 📝",
                "'{$room->title}' was submitted and is pending admin approval.",
                ['type' => 'property_submitted', 'room_id' => (string) $room->id],
                $actionUrl
            );

            // 2. Email Notification
            Mail::to($broker->email)->send(new BrandedMessageMail(
                "Property Submitted for Approval: {$room->title} 📝",
                "Property Listing Submitted Successfully",
                "Your property listing '{$room->title}' has been received and is currently under review by the ApnaNest verification team. We usually process listings within a few hours.",
                "Listing Submitted",
                "View My Properties",
                $actionUrl,
                [
                    'Property Title' => $room->title,
                    'City'           => $room->city ?? 'N/A',
                    'Price'          => '₹' . number_format($room->rent ?? 0, 2),
                    'Status'         => 'Pending Approval',
                ],
                'primary',
                'You will receive an email notification as soon as your listing is approved and goes live.'
            ));
        } catch (\Exception $e) {
            Log::error("NotificationService notifyPropertySubmitted error: " . $e->getMessage());
        }
    }

    /**
     * Notify User when they submit a new complaint / support ticket.
     */
    public static function notifyComplaintSubmitted(User $user, $complaint): void
    {
        try {
            if (!$user || !$user->email) return;

            $ticketNumber = $complaint->ticket_number ?? "TKT-{$complaint->id}";
            $actionUrl    = route('home');

            // 1. Bell notification
            try {
                UserNotification::send(
                    $user->id,
                    'complaint_submitted',
                    "Complaint Submitted: #{$ticketNumber} 🎫",
                    "Your complaint #{$ticketNumber} has been received and is being reviewed by our support team.",
                    $actionUrl,
                    'fa-headset'
                );
            } catch (\Exception $e) {
                Log::warning("Complaint submission bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push Notification
            FirebaseService::sendToUser(
                $user,
                "Complaint Ticket Submitted 🎫",
                "Ticket #{$ticketNumber} received and under review.",
                ['type' => 'complaint_submitted', 'ticket' => (string) $ticketNumber],
                $actionUrl
            );

            // 2. Email Notification
            Mail::to($user->email)->send(new BrandedMessageMail(
                "Complaint Ticket Received: #{$ticketNumber} 🎫",
                "Complaint Ticket Received",
                "Thank you for contacting ApnaNest Support. We have received your complaint ticket (#{$ticketNumber}). Our dedicated support team is currently reviewing your issue and will get back to you shortly.",
                "Support Ticket Acknowledgement",
                "View My Account",
                $actionUrl,
                [
                    'Ticket Number' => "#{$ticketNumber}",
                    'Subject'       => $complaint->subject ?? 'N/A',
                    'Category'      => ucfirst($complaint->category ?? 'General'),
                    'Status'        => 'Submitted (Under Review)',
                    'Submitted At'  => now()->format('d M Y, h:i A'),
                ],
                'primary',
                'We aim to respond to all support queries within 24 hours.'
            ));
        } catch (\Exception $e) {
            Log::error("NotificationService notifyComplaintSubmitted error: " . $e->getMessage());
        }
    }

    /**
     * Notify User when they receive a Referral or Bonus Free Contact Unlock Credit.
     */
    public static function notifyReferralBonusReceived(User $user, int $credits = 1, ?string $reason = null): void
    {
        try {
            if (!$user || !$user->email) return;

            $actionUrl = route('home');
            $reasonText = $reason ?: 'A friend joined ApnaNest using your referral code!';

            // 1. Bell notification
            try {
                UserNotification::send(
                    $user->id,
                    'referral_bonus',
                    "Free Unlock Bonus Received! 🎁",
                    "{$reasonText} You received {$credits} Free Contact Unlock Credit(s).",
                    $actionUrl,
                    'fa-gift'
                );
            } catch (\Exception $e) {
                Log::warning("Referral bonus bell notification failed: " . $e->getMessage());
            }

            // 2. Firebase Push
            FirebaseService::sendToUser(
                $user,
                "Free Contact Credit Earned! 🎁",
                "{$reasonText} You have +{$credits} Free Unlock Credit(s) available.",
                ['type' => 'referral_bonus'],
                $actionUrl
            );

            // 3. Email Notification
            Mail::to($user->email)->send(new BrandedMessageMail(
                "Congratulations! Free Contact Unlock Credit Received 🎁",
                "Free Contact Credit Added to Your Account!",
                "Great news! You have earned {$credits} Free Contact Unlock Credit(s) on ApnaNest. {$reasonText}",
                "Bonus Reward",
                "Explore & Unlock Properties",
                $actionUrl,
                [
                    'Bonus Credit'        => "+{$credits} Free Unlock(s)",
                    'Total Free Unlocks' => (string) ($user->free_unlocks ?? 1),
                    'Reason'             => $reasonText,
                ],
                'success',
                'Use your free credits to instantly unlock owner phone numbers for any verified property listing on ApnaNest!'
            ));
        } catch (\Exception $e) {
            Log::error("NotificationService notifyReferralBonusReceived error: " . $e->getMessage());
        }
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    public const CATEGORIES = [
        'fake_listing' => 'Fake or misleading listing',
        'wrong_information' => 'Wrong property information',
        'property_unavailable' => 'Property already rented/unavailable',
        'owner_conduct' => 'Owner conduct or harassment',
        'user_conduct' => 'User conduct or harassment',
        'fraud_payment' => 'Fraud or suspicious payment demand',
        'contact_unlock' => 'Contact unlock issue',
        'privacy' => 'Privacy or contact misuse',
        'technical' => 'Technical or payment issue',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'submitted' => 'Submitted', 'under_review' => 'Under Review',
        'need_information' => 'Need Information', 'resolved' => 'Resolved',
        'rejected' => 'Rejected', 'closed' => 'Closed',
    ];

    public const RESOLUTION_CATEGORIES = ['information_provided'=>'Information provided','listing_corrected'=>'Listing corrected','account_action'=>'Account action taken','refund_or_payment'=>'Payment/refund resolved','technical_fix'=>'Technical fix','no_violation'=>'No violation found','other'=>'Other'];

    protected $fillable = ['ticket_number', 'user_id', 'room_id', 'against_user_id', 'assigned_to', 'category', 'subject', 'description', 'evidence_path', 'priority', 'status', 'resolution', 'resolution_category', 'due_at', 'escalated_at', 'closed_at', 'reopened_at'];

    protected $casts = ['closed_at' => 'datetime', 'due_at' => 'datetime', 'escalated_at' => 'datetime', 'reopened_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            if (!$complaint->ticket_number) {
                do {
                    $ticket = 'CMP-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
                } while (static::where('ticket_number', $ticket)->exists());
                $complaint->ticket_number = $ticket;
            }
            if (!$complaint->due_at) {
                $complaint->due_at = now()->addHours(match($complaint->priority) {'urgent'=>4,'high'=>12,'medium'=>24,default=>48});
            }
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function againstUser() { return $this->belongsTo(User::class, 'against_user_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies() { return $this->hasMany(ComplaintReply::class); }
    public function activities() { return $this->hasMany(ComplaintActivity::class); }
}

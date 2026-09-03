<?php

namespace App\Http\Controllers\Api;

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApiComplaintController extends BaseApiController
{
    public function options()
    {
        return $this->sendSuccess([
            'categories' => Complaint::CATEGORIES,
            'statuses' => Complaint::STATUSES,
            'priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'],
        ]);
    }

    public function index(Request $request)
    {
        $query = Complaint::where('user_id', $request->user()->id)->with('room:id,slug,title');
        if ($request->filled('status')) {
            $request->validate(['status' => [Rule::in(array_keys(Complaint::STATUSES))]]);
            $query->where('status', $request->status);
        }

        return $this->sendSuccess(
            $query->latest()->paginate(max(1, min(50, $request->integer('limit', 15)))),
            'Complaints fetched successfully'
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(Complaint::CATEGORIES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('complaints/evidence', 'local');
        }
        if (!empty($data['room_id'])) {
            $data['against_user_id'] = Room::find($data['room_id'])?->user_id;
        }
        $data['priority'] ??= 'medium';
        unset($data['evidence']);

        $complaint = $request->user()->complaints()->create($data);
        $complaint->activities()->create([
            'actor_id'   => $request->user()->id,
            'type'       => 'created',
            'status_to'  => 'submitted',
            'description'=> 'Complaint submitted.',
        ]);

        // Notify admin (API side)
        try {
            \App\Models\AdminNotification::send(
                'complaint_submitted',
                'New Complaint (App)',
                $request->user()->name . ': ' . \Illuminate\Support\Str::limit($complaint->subject, 55),
                route('admin.complaints.show', $complaint->id),
                'fa-triangle-exclamation'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->sendSuccess($complaint->load('room:id,slug,title'), 'Complaint submitted successfully', 201);
    }

    public function show(Request $request, Complaint $complaint)
    {
        $this->authorizeReporter($request, $complaint);
        $complaint->load([
            'room:id,slug,title',
            'replies' => fn ($q) => $q->where('is_internal', false)->with('user:id,name,role,avatar'),
            'activities' => fn ($q) => $q->where('is_internal', false)->latest(),
        ]);

        return $this->sendSuccess($complaint, 'Complaint fetched successfully');
    }

    public function reply(Request $request, Complaint $complaint)
    {
        $this->authorizeReporter($request, $complaint);
        if (in_array($complaint->status, ['closed', 'rejected'], true)) {
            return $this->sendError('This complaint is closed.', [], 422);
        }
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('complaints/replies', 'local');
        }
        unset($data['attachment']);
        $data['user_id'] = $request->user()->id;
        $reply = $complaint->replies()->create($data);
        $complaint->activities()->create(['actor_id' => $request->user()->id, 'type' => 'reply', 'description' => 'Reporter added information.']);
        if ($complaint->status === 'need_information') $complaint->update(['status' => 'under_review']);

        return $this->sendSuccess($reply->load('user:id,name,role,avatar'), 'Reply added successfully', 201);
    }

    public function evidence(Request $request, Complaint $complaint)
    {
        $this->authorizeReporter($request, $complaint);
        abort_unless($complaint->evidence_path && Storage::disk('local')->exists($complaint->evidence_path), 404);
        return Storage::disk('local')->download($complaint->evidence_path);
    }

    public function attachment(Request $request, Complaint $complaint, ComplaintReply $reply)
    {
        $this->authorizeReporter($request, $complaint);
        abort_unless($reply->complaint_id === $complaint->id && !$reply->is_internal, 404);
        abort_unless($reply->attachment_path && Storage::disk('local')->exists($reply->attachment_path), 404);
        return Storage::disk('local')->download($reply->attachment_path);
    }

    private function authorizeReporter(Request $request, Complaint $complaint): void
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;

class AnalyticsEventController extends Controller
{
    private const ALLOWED_EVENTS = [
        'PageView',
        'Search',
        'ViewContent',
        'InitiateCheckout',
        'Purchase',
    ];

    public function store(Request $request)
    {
        if ($request->user()?->role === 'admin') {
            return response()->json(['success' => true, 'skipped' => true]);
        }

        $data = $request->validate([
            'event_name' => ['required', 'string', 'max:60', 'in:'.implode(',', self::ALLOWED_EVENTS)],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'url' => ['nullable', 'string', 'max:1000'],
            'referrer' => ['nullable', 'string', 'max:1000'],
            'payload' => ['nullable', 'array'],
        ]);

        $payload = $data['payload'] ?? [];
        $roomId = $data['room_id'] ?? null;
        if (!$roomId && isset($payload['content_ids'][0]) && is_numeric($payload['content_ids'][0])) {
            $roomId = (int) $payload['content_ids'][0];
        }

        AnalyticsEvent::create([
            'event_name' => $data['event_name'],
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'room_id' => $roomId,
            'payment_id' => $data['payment_id'] ?? null,
            'city' => $data['city'] ?? $payload['city'] ?? null,
            'amount' => $data['amount'] ?? $payload['value'] ?? null,
            'currency' => $data['currency'] ?? $payload['currency'] ?? 'INR',
            'url' => $data['url'] ?? $request->headers->get('referer'),
            'referrer' => $data['referrer'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload' => $payload,
        ]);

        return response()->json(['success' => true]);
    }
}

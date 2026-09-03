<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? now()->subDays(29)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        // Top 10 searched cities
        // Exclude null or empty city searches
        $topCities = SearchLog::whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->limit(10)
            ->get();

        // Top Cities by Owner Listings (Supply)
        $topListingCities = \App\Models\Room::whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();


        // Recent Logs
        $logsQuery = SearchLog::with('user');
        if ($request->filled('from')) $logsQuery->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $logsQuery->whereDate('created_at', '<=', $request->date('to'));
        if ($request->filled('city')) $logsQuery->where('city', 'like', '%'.$request->city.'%');
        $recentLogs = $logsQuery->orderByDesc('created_at')
            ->paginate(20);

        $eventsBase = AnalyticsEvent::whereBetween('created_at', [$from, $to]);
        $eventCounts = (clone $eventsBase)
            ->select('event_name', DB::raw('COUNT(*) as total'))
            ->groupBy('event_name')
            ->pluck('total', 'event_name');

        $visitorStats = [
            'page_views' => (int) ($eventCounts['PageView'] ?? 0),
            'unique_visitors' => (int) (clone $eventsBase)->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
            'room_views' => (int) ($eventCounts['ViewContent'] ?? 0),
            'searches' => (int) ($eventCounts['Search'] ?? 0),
            'checkout_starts' => (int) ($eventCounts['InitiateCheckout'] ?? 0),
            'purchases' => (int) ($eventCounts['Purchase'] ?? 0),
            'revenue' => (float) (clone $eventsBase)->where('event_name', 'Purchase')->sum('amount'),
        ];
        $visitorStats['checkout_conversion'] = $visitorStats['checkout_starts'] > 0
            ? round(($visitorStats['purchases'] / $visitorStats['checkout_starts']) * 100, 1)
            : 0;

        $topViewedRooms = AnalyticsEvent::with('room')
            ->whereBetween('created_at', [$from, $to])
            ->where('event_name', 'ViewContent')
            ->whereNotNull('room_id')
            ->select('room_id', DB::raw('COUNT(*) as total'))
            ->groupBy('room_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $eventCities = AnalyticsEvent::whereBetween('created_at', [$from, $to])
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('COUNT(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $eventsQuery = AnalyticsEvent::with(['user', 'room', 'payment']);
        if ($request->filled('from')) $eventsQuery->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $eventsQuery->whereDate('created_at', '<=', $request->date('to'));
        if ($request->filled('event')) $eventsQuery->where('event_name', $request->event);
        if ($request->filled('city')) $eventsQuery->where('city', 'like', '%'.$request->city.'%');
        $recentEvents = $eventsQuery->orderByDesc('created_at')->paginate(25, ['*'], 'events_page');

        return view('admin.search_logs.index', compact(
            'topCities',
            'recentLogs',
            'topListingCities',
            'visitorStats',
            'topViewedRooms',
            'eventCities',
            'recentEvents',
            'from',
            'to'
        ));
    }

    public function destroy(SearchLog $searchLog)
    {
        $searchLog->delete();
        return back()->with('success', 'Search log deleted.');
    }

    public function destroyRange(Request $request)
    {
        $data = $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);
        $count = SearchLog::whereDate('created_at', '>=', $data['from'])->whereDate('created_at', '<=', $data['to'])->delete();
        return redirect()->route('admin.analytics', ['tab' => 'logs'])->with('success', $count.' search logs deleted.');
    }

    public function destroyAll()
    {
        $count = SearchLog::query()->delete();
        return redirect()->route('admin.analytics', ['tab' => 'logs'])->with('success', $count.' search logs deleted.');
    }
}

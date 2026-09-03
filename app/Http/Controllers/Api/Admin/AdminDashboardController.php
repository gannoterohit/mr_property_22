<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Models\SearchLog;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends BaseApiController
{
    /**
     * Admin Dashboard Statistics
     */
    public function index(Request $request)
    {
        $admin = $request->user();
        $access = [
            'listings' => $admin->hasAdminPermission('listings.view'),
            'people' => $admin->hasAdminPermission('people.view'),
            'support' => $admin->hasAdminPermission('support.view'),
            'finance' => $admin->hasAdminPermission('finance.view'),
            'content' => $admin->hasAdminPermission('content.view'),
            'reports' => $admin->hasAdminPermission('reports.view'),
            'settings' => $admin->hasAdminPermission('settings.manage'),
            'staff' => $admin->hasAdminPermission('staff.manage'),
            'activity' => $admin->hasAdminPermission('activity.view'),
        ];
        $data = ['access' => $access, 'modules' => [], 'available_actions' => []];

        if ($access['listings']) {
            $data['modules']['listings'] = [
                'total_paid' => Room::where('listing_fee_paid', true)->count(),
                'active' => Room::where('status', 'active')->where('listing_fee_paid', true)->count(),
                'pending' => Room::where('listing_status', 'pending')->count(),
                'approved' => Room::where('listing_status', 'approved')->count(),
                'rejected' => Room::where('listing_status', 'rejected')->count(),
                'recent' => RoomResource::collection(Room::with('owner')->latest()->limit(5)->get()),
            ];
            $data['available_actions'][] = 'listings.open';
            if ($admin->hasAdminPermission('listings.manage')) $data['available_actions'][] = 'listings.manage';
        }
        if ($access['people']) {
            $data['modules']['people'] = [
                'users' => User::where('role', 'user')->count(),
                'owners' => User::where('role', 'owner')->count(),
                'recent_users' => User::where('role', 'user')->latest()->limit(5)->get(['id','name','email','created_at']),
                'recent_owners' => User::where('role', 'owner')->withCount('rooms')->latest()->limit(5)->get(['id','name','email','created_at']),
            ];
            $data['available_actions'][] = 'people.open';
            if ($admin->hasAdminPermission('people.manage')) $data['available_actions'][] = 'people.manage';
        }
        if ($access['support']) {
            $data['modules']['support'] = [
                'open_complaints' => \App\Models\Complaint::whereNotIn('status',['resolved','rejected','closed'])->count(),
                'unread_contacts' => \App\Models\ContactMessage::where('is_read', false)->count(),
                'recent_tickets' => \App\Models\Complaint::latest()->limit(5)->get(['id','ticket_number','subject','priority','status','created_at']),
            ];
            $data['available_actions'][] = 'support.open';
            if ($admin->hasAdminPermission('support.manage')) $data['available_actions'][] = 'support.manage';
        }
        if ($access['finance']) {
            $types = ['listing','featured','unlock','subscription'];
            $completed = Payment::where('status','completed')->whereIn('type',$types);
            $monthSql = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
            $monthly = (clone $completed)->selectRaw("{$monthSql} month, SUM(amount) total")->whereYear('created_at',now()->year)->groupByRaw($monthSql)->pluck('total','month');
            $data['modules']['finance'] = [
                'total' => (float)(clone $completed)->sum('amount'),
                'today' => (float)(clone $completed)->whereDate('created_at',today())->sum('amount'),
                'monthly_revenue' => collect(range(1,12))->map(fn($month)=>(float)($monthly[$month]??0))->all(),
                'recent_payments' => Payment::with('user')->latest()->limit(5)->get(),
            ];
            $data['available_actions'][] = 'finance.open';
            if ($admin->hasAdminPermission('finance.manage')) $data['available_actions'][] = 'finance.manage';
        }
        if ($access['content']) {
            $data['modules']['content'] = [
                'blogs' => \App\Models\Blog::count(),
                'offers' => \App\Models\Offer::count(),
                'pages' => \App\Models\CmsPage::count(),
            ];
            $data['available_actions'][] = 'content.open';
            if ($admin->hasAdminPermission('content.manage')) $data['available_actions'][] = 'content.manage';
        }
        if ($access['reports']) {
            $data['modules']['reports'] = [
                'searches_today' => SearchLog::whereDate('created_at',today())->count(),
                'unlocks_today' => \App\Models\Enquiry::where('unlocked',true)->whereDate('unlocked_at',today())->count(),
            ];
            $data['available_actions'][] = 'reports.open';
        }
        if ($access['settings']) $data['available_actions'][] = 'settings.manage';
        if ($access['staff']) $data['available_actions'][] = 'staff.manage';
        if ($access['activity']) $data['available_actions'][] = 'activity.open';

        return $this->sendSuccess($data);
    }

    /**
     * Analytics summary
     */
    public function analytics()
    {
        $topCities = Room::where('status', 'active')
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $unlockData = [];
        for ($i = 1; $i <= 12; $i++) {
            $unlockData[] = 0;
        }

        $usersPerMonth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->where('role', 'user')
            ->groupBy('month')
            ->pluck('count', 'month');

        $userData = [];
        for ($i = 1; $i <= 12; $i++) {
            $userData[] = (int) ($usersPerMonth[$i] ?? 0);
        }

        return $this->sendSuccess([
            'top_cities'       => $topCities,
            'unlocks_monthly' => $unlockData,
            'users_monthly'    => $userData,
        ]);
    }

    /**
     * Detailed search analytics
     */
    public function searchAnalytics(Request $request)
    {
        $topSearchedCities = SearchLog::whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topListingCities = Room::whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $recentLogs = SearchLog::with('user')
            ->orderByDesc('created_at')
            ->paginate($request->get('limit', 20));

        return $this->sendSuccess([
            'top_searched_cities' => $topSearchedCities,
            'top_listing_cities'  => $topListingCities,
            'recent_logs'         => $recentLogs,
        ]);
    }
}

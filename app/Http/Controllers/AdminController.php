<?php
namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Payout;
use App\Models\Setting;
use App\Models\RoomOption;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Mail\RoomApprovedMail;
use App\Mail\RoomRejectedMail;
use App\Models\RejectionReason;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
   
    
    public function dashboard()
    {
        // Counts
        $rooms = Room::where('listing_fee_paid', true)->count();
        $users = User::count();
        $owners = User::where('role', 'owner')->count();
    
        $activeRooms = Room::where('status', 'active')
            ->where('listing_fee_paid', true)
            ->count();
    
        // Room status
        $approvedRooms = Room::where('listing_status', 'approved')->count();
        $pendingRooms  = Room::where('listing_status', 'pending')->count();
        $rejectedRooms = Room::where('listing_status', 'rejected')->count();
    
        // Earnings
        // Direct-contact platform revenue; legacy booking payments are intentionally excluded.
        $totalEarnings = Payment::where('status', 'completed')
            ->whereIn('type', ['listing', 'featured', 'unlock', 'subscription'])
            ->sum('amount');
        $todayEarnings = Payment::where('status', 'completed')
            ->whereIn('type', ['listing', 'featured', 'unlock', 'subscription'])
            ->whereDate('created_at', today())->sum('amount');
    
        $listingEarnings = Payment::where('status', 'completed')
            ->where('type', 'listing')
            ->sum('amount');
            
        $unlockEarnings = Payment::where('status', 'completed')
            ->where('type', 'unlock')
            ->sum('amount');

        $featuredEarnings = Payment::where('status', 'completed')
            ->where('type', 'featured')
            ->sum('amount');

        $bookingEarnings = Payment::where('status', 'completed')
        ->where('type', 'booking')
        ->sum('amount');

    $subscriptionEarnings = Payment::where('status', 'completed')
        ->where('type', 'subscription')
        ->sum('amount');
    
        // Monthly revenue graph
        $monthlyRevenue = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->where('status', 'completed')
            ->whereIn('type', ['listing', 'featured', 'unlock', 'subscription'])
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month');
    
        $revenueData = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueData[] = $monthlyRevenue[$i] ?? 0;
        }
        
        // Monthly comparison
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;
        
        $currentMonthEarnings = Payment::where('status', 'completed')
            ->whereIn('type', ['listing', 'featured', 'unlock', 'subscription'])
            ->whereMonth('created_at', $currentMonth)
            ->sum('amount');
            
        $lastMonthEarnings = Payment::where('status', 'completed')
            ->whereIn('type', ['listing', 'featured', 'unlock', 'subscription'])
            ->whereMonth('created_at', $lastMonth)
            ->sum('amount');
            
        $percentageChange = $lastMonthEarnings > 0 
            ? (($currentMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100 
            : 0;
    
        // Recents
        $recentRooms = Room::with('owner')->latest()->limit(5)->get();
        $recentPayments = Payment::with('user')->latest()->limit(5)->get();
        $recentUsers = User::where('role', 'user')->latest()->limit(5)->get();
        $recentOwners = User::where('role', 'owner')->withCount('rooms')->latest()->limit(5)->get();

        $actionQueues = [
            ['label'=>'Pending room approvals','count'=>Room::where('listing_status','pending')->count(),'route'=>route('admin.all-rooms',['listing_status'=>'pending']),'icon'=>'fa-house-circle-exclamation','tone'=>'amber'],
            ['label'=>'Pending KYC','count'=>User::where('role','owner')->where('verification_status','pending')->count(),'route'=>route('admin.owners',['verification_status'=>'pending']),'icon'=>'fa-id-card','tone'=>'blue'],
            ['label'=>'Unresolved complaints','count'=>\App\Models\Complaint::whereNotIn('status',['resolved','rejected','closed'])->count(),'route'=>route('admin.complaints.index',['status'=>'open']),'icon'=>'fa-shield-halved','tone'=>'red'],
            ['label'=>'Failed / pending payments','count'=>Payment::whereIn('status',['failed','pending'])->count(),'route'=>route('admin.payments.index',['status'=>'pending']),'icon'=>'fa-credit-card','tone'=>'red'],
            ['label'=>'Subscriptions expiring in 7 days','count'=>Subscription::where('status','active')->whereBetween('end_date',[today(),today()->addDays(7)])->count(),'route'=>route('admin.reports'),'icon'=>'fa-hourglass-half','tone'=>'amber'],
            ['label'=>'Unread contact enquiries','count'=>\App\Models\ContactMessage::where('is_read',false)->count(),'route'=>route('admin.contact-messages.index'),'icon'=>'fa-envelope','tone'=>'blue'],
            ['label'=>"Today's contact unlocks",'count'=>\App\Models\Enquiry::where('unlocked',true)->whereDate('unlocked_at',today())->count(),'route'=>route('admin.reports'),'icon'=>'fa-lock-open','tone'=>'green'],
        ];
    
        return view('admin.dashboard', compact(
            'rooms','users','owners','activeRooms',
            'approvedRooms','pendingRooms','rejectedRooms',
            'totalEarnings','todayEarnings','listingEarnings','unlockEarnings','featuredEarnings','bookingEarnings','subscriptionEarnings',
            'revenueData',
            'currentMonthEarnings','lastMonthEarnings','percentageChange',
            'recentRooms','recentPayments','recentUsers','recentOwners','actionQueues'
        ));
    }
    
    
    public function Rooms(Request $request)
    {
        $query = Room::with(['owner','roomTypeOption'])->whereHas('owner');
        if ($request->filled('search')) $query->where(fn($q)=>$q->where('title','like','%'.$request->search.'%')->orWhere('city','like','%'.$request->search.'%'));
        if ($request->filled('listing_status')) $request->listing_status === 'expired' ? $query->where('expires_at','<',now()) : $query->where('listing_status',$request->listing_status);
        if ($request->filled('moderation_status')) $query->where('moderation_status',$request->moderation_status);
        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('city')) $query->where('city',$request->city);
        if ($request->filled('room_type')) $query->where('room_type_option_id',$request->room_type);
        if ($request->filled('kyc')) $query->whereHas('owner',fn($q)=>$q->where('verification_status',$request->kyc));
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;
        $allrooms = $query->latest()->paginate($perPage)->withQueryString();
        $rejectionReasons = RejectionReason::where('is_active', true)->get();
        $cities=Room::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        return view('admin.all-room', compact('allrooms', 'rejectionReasons','cities'));
    }

    public function bulkRooms(Request $request)
    {
        $data=$request->validate(['room_ids'=>'required|array|min:1','room_ids.*'=>'exists:rooms,id','action'=>'required|in:approve,suspend,activate,mark_reported']);
        $updates=match($data['action']) {'approve'=>['listing_status'=>'approved','moderation_status'=>'normal'],'suspend'=>['moderation_status'=>'suspended'],'activate'=>['moderation_status'=>'normal','status'=>'active'],'mark_reported'=>['moderation_status'=>'reported']};
        Room::whereIn('id',$data['room_ids'])->update($updates);
        return back()->with('success',count($data['room_ids']).' listings updated.');
    }

    public function approveRoom(Room $room)
    {
        try {
            // Update room status
            $room->listing_status = 'approved';
            $room->save();

            // Send email to owner (try-catch to prevent failure if mail not configured)
            try {
                $owner = $room->owner;
                Mail::to($owner->email)->send(new RoomApprovedMail($room, $owner));
                
                // Trigger City Alerts
                $alerts = \App\Models\CityAlert::with('user')->where('city', $room->city)->get();
                foreach ($alerts as $alert) {
                    if ($alert->user && $alert->user->email) {
                        Mail::to($alert->user->email)->send(new \App\Mail\NewRoomInCityAlert($room, $room->city));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Mail sending failed: ' . $e->getMessage());
                // Continue execution even if mail fails
            }

            return response()->json(['success' => true, 'message' => 'Room approved successfully']);
        } catch (\Exception $e) {
            \Log::error('Room approval error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error approving room: ' . $e->getMessage()], 500);
        }
    }

    public function rejectRoom(Request $request, Room $room)
    {
        try {
            $request->validate([
                'reasons' => 'required|array|min:1',
                'reasons.*' => 'exists:rejection_reasons,id',
                'customReason' => 'nullable|string|max:500'
            ]);

            // Update room status
            $room->listing_status = 'rejected';
            $room->save();

            // Save rejection reasons
            if (!empty($request->reasons)) {
                $room->rejectionReasons()->sync($request->reasons);
            }

            // Get all rejection reasons for email
            $reasons = [];
            if (!empty($request->reasons)) {
                $selectedReasons = RejectionReason::whereIn('id', $request->reasons)->get();
                foreach ($selectedReasons as $reason) {
                    $reasons[] = $reason->reason;
                }
            }
            
            if (!empty($request->customReason)) {
                $reasons[] = $request->customReason;
            }

            // Send email to owner
            $owner = $room->owner;
            try {
                if ($owner?->email) Mail::to($owner->email)->send(new RoomRejectedMail($room, $owner, $reasons));
            } catch (\Exception $mailError) {
                \Log::warning('Room rejection email failed: '.$mailError->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Room rejected successfully']);
        } catch (\Exception $e) {
            \Log::error('Room rejection error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error rejecting room: ' . $e->getMessage()], 500);
        }
    }

    public function deleteRoom(Room $room)
    {
        try {
            foreach (($room->photos ?? []) as $photo) {
                if ($photo && !str_starts_with($photo, 'http')) Storage::disk('public')->delete($photo);
            }
            if ($room->photo && !str_starts_with($room->photo, 'http') && !in_array($room->photo, $room->photos ?? [], true)) {
                Storage::disk('public')->delete($room->photo);
            }
            if ($room->video && !str_starts_with($room->video, 'http')) Storage::disk('public')->delete($room->video);
            $room->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Room deletion error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting room: ' . $e->getMessage()], 500);
        }
    }

    public function reports(Request $request)
    {
        $from=$request->date('from') ?? now()->startOfMonth(); $to=$request->date('to') ?? now()->endOfDay();
        $paymentsBase=Payment::whereBetween('created_at',[$from->startOfDay(),$to->endOfDay()]);
        $revenueByType=(clone $paymentsBase)->where('status','completed')->selectRaw('type, SUM(amount) total')->groupBy('type')->pluck('total','type');
        $dailyCollections=(clone $paymentsBase)->where('status','completed')->selectRaw('DATE(created_at) day, SUM(amount) total')->groupBy('day')->orderBy('day')->get();
        $failedPayments=(clone $paymentsBase)->where('status','failed')->count();
        $totalUsers=User::where('role','user')->whereBetween('created_at',[$from,$to])->count();
        $unlocks=\App\Models\Enquiry::where('unlocked',true)->whereBetween('unlocked_at',[$from,$to])->count();
        $cityDemand=\App\Models\Enquiry::join('rooms','rooms.id','=','enquiries.room_id')->whereBetween('enquiries.created_at',[$from,$to])->selectRaw('rooms.city, COUNT(*) total')->groupBy('rooms.city')->orderByDesc('total')->limit(10)->get();
        $ownerGrowth=User::where('role','owner')->whereBetween('created_at',[$from,$to])->count();
        $listingGrowth=Room::whereBetween('created_at',[$from,$to])->count();
        $resolutionHours=\App\Models\Complaint::whereNotNull('closed_at')->whereBetween('closed_at',[$from,$to])->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) avg_hours')->value('avg_hours');
        return view('admin.reports',compact('from','to','revenueByType','dailyCollections','failedPayments','totalUsers','unlocks','cityDemand','ownerGrowth','listingGrowth','resolutionHours'));
    }

    public function payouts()
    {
        $payouts = Payout::with(['owner', 'booking'])
            ->latest()
            ->paginate(20);

        return view('admin.payouts', compact('payouts'));
    }

    public function processPayout(Request $request, $id)
    {
        $payout = Payout::findOrFail($id);
        
        if ($payout->status !== 'pending') {
            return back()->with('error', 'Payout already processed!');
        }

        if ($payout->release_date > now()) {
            return back()->with('error', 'Payout is still on hold period!');
        }

        $payout->update([
            'status' => 'processed',
            'payment_reference' => $request->payment_reference
        ]);

        return back()->with('success', 'Payout processed successfully!');
    }

    public function users(Request $request)
    {
        $query = User::withTrashed()->where('role', 'user');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->status === 'blocked') $query->where('is_blocked',true);
        if ($request->status === 'active') $query->where('is_blocked',false)->whereNull('deleted_at');
        if ($request->status === 'deleted') $query->onlyTrashed();
        
        $users = $query->latest()->paginate(10);
        $memberStats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('is_blocked', false)->count(),
            'blocked' => User::where('role', 'user')->where('is_blocked', true)->count(),
            'deleted' => User::onlyTrashed()->where('role', 'user')->count(),
        ];
        return view('admin.users', compact('users', 'memberStats'));
    }

    public function member360(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $matches = collect();

        if ($term !== '') {
            $matches = User::withTrashed()
                ->whereIn('role', ['user', 'owner'])
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('referral_code', 'like', "%{$term}%");
                    if (ctype_digit($term)) {
                        $query->orWhere('id', (int) $term);
                    }
                })
                ->withCount(['rooms', 'payments', 'enquiries', 'complaints'])
                ->latest()
                ->limit(20)
                ->get();
        }

        $member = null;
        $history = [];
        if ($request->filled('member_id')) {
            $member = User::withTrashed()
                ->whereIn('role', ['user', 'owner'])
                ->withCount(['rooms', 'payments', 'subscriptions', 'enquiries', 'bookings', 'wishlists', 'complaints', 'cityAlerts', 'referrals'])
                ->findOrFail($request->integer('member_id'));

            $history = [
                'rooms' => Room::where('user_id', $member->id)->latest()->limit(25)->get(),
                'payments' => \App\Models\Payment::where('user_id', $member->id)->latest()->limit(25)->get(),
                'subscriptions' => \App\Models\Subscription::with('plan')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'enquiries' => \App\Models\Enquiry::with('room')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'bookings' => \App\Models\Booking::with('room')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'complaints' => \App\Models\Complaint::with(['room', 'againstUser'])
                    ->where(fn ($query) => $query->where('user_id', $member->id)->orWhere('against_user_id', $member->id))
                    ->latest()->limit(25)->get(),
                'wishlists' => \App\Models\Wishlist::with('room')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'city_alerts' => \App\Models\CityAlert::where('user_id', $member->id)->latest()->limit(25)->get(),
                'referrals' => User::withTrashed()->where('referred_by_id', $member->id)->latest()->limit(25)->get(),
                'activities' => \App\Models\AdminActivityLog::with('actor')
                    ->where('subject_type', User::class)->where('subject_id', $member->id)
                    ->latest()->limit(25)->get(),
            ];
        }

        return view('admin.members.index', compact('term', 'matches', 'member', 'history'));
    }

    public function owners(Request $request)
    {
        $query = User::withTrashed()->where('role', 'owner')->withCount('rooms');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('verification_status')) $query->where('verification_status',$request->verification_status);
        if ($request->status === 'active') $query->where('is_blocked', false)->whereNull('deleted_at');
        if ($request->status === 'blocked') $query->where('is_blocked',true);
        if ($request->status === 'deleted') $query->onlyTrashed();
        
        $owners = $query->latest()->paginate(10);
        $memberStats = [
            'total' => User::where('role', 'owner')->count(),
            'verified' => User::where('role', 'owner')->where('verification_status', 'verified')->count(),
            'blocked' => User::where('role', 'owner')->where('is_blocked', true)->count(),
            'deleted' => User::onlyTrashed()->where('role', 'owner')->count(),
        ];
        return view('admin.owners', compact('owners', 'memberStats'));
    }

    public function userDetail(User $user)
    {
        abort_unless($user->role === 'user', 404);
        $user->load(['payments','subscriptions.plan','complaints','enquiries.room','adminActivities.actor']);
        return view('admin.user-detail', compact('user'));
    }

    public function ownerDetail(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->load(['rooms','payments','subscriptions.plan','complaints','adminActivities.actor']);
        $rooms = $owner->rooms()->latest()->paginate(10);
        return view('admin.owner-detail', compact('owner', 'rooms'));
    }

    public function toggleBlock(Request $request, User $user)
    {
        $request->validate(['block_reason'=>'nullable|string|max:255']);
        $blocking=!$user->is_blocked;
        $user->update([
            'is_blocked' => $blocking,
            'block_reason' => $blocking ? ($request->block_reason ?: 'Blocked by administrator') : null,
        ]);

        $status = $user->is_blocked ? 'blocked' : 'unblocked';
        return back()->with('success', "User {$status} successfully!");
    }

    public function updateMemberNotes(Request $request, User $user)
    {
        $data=$request->validate(['admin_notes'=>'nullable|string|max:5000','verification_status'=>'required|in:pending,under_review,verified,rejected']);
        $data['is_verified']=$data['verification_status']==='verified'; $data['verified_at']=$data['is_verified']?now():null; $user->update($data);
        return back()->with('success','Member notes and verification updated.');
    }

    public function restoreMember(int $user)
    {
        $member=User::withTrashed()->findOrFail($user); $member->restore(); return back()->with('success','Account restored.');
    }

    public function createUser()
    {
        return view('admin.members.form', ['member' => new User(), 'memberRole' => 'user']);
    }

    public function storeUser(Request $request)
    {
        $user = $this->createMember($request, 'user');
        return redirect()->route('admin.users.detail', $user)->with('success', 'User account created successfully.');
    }

    public function editUser(User $user)
    {
        abort_unless($user->role === 'user', 404);
        return view('admin.members.form', ['member' => $user, 'memberRole' => 'user']);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($user->role === 'user', 404);
        $this->updateMember($request, $user);
        return redirect()->route('admin.users.detail', $user)->with('success', 'User account updated successfully.');
    }

    public function destroyUser(User $user)
    {
        abort_unless($user->role === 'user', 404);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User account deleted. It can be restored from the Deleted filter.');
    }


    public function cityAlerts(Request $request)
    {
        $query=\App\Models\CityAlert::with('user');
        if($request->filled('search')){$term=trim($request->search);$query->where(fn($q)=>$q->where('city','like',"%{$term}%")->orWhereHas('user',fn($u)=>$u->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%")));}
        if($request->filled('city'))$query->where('city',$request->city);
        if($request->filled('from'))$query->whereDate('created_at','>=',$request->date('from'));
        if($request->filled('to'))$query->whereDate('created_at','<=',$request->date('to'));
        $alerts=$query->latest()->paginate(15)->withQueryString();
        $cities=\App\Models\CityAlert::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        $cityStats=\App\Models\CityAlert::selectRaw('city,COUNT(*) total')->groupBy('city')->orderByDesc('total')->limit(4)->get();
        return view('admin.city-alerts.index',compact('alerts','cities','cityStats'));
    }

    public function deleteCityAlert($id)
    {
        $alert = \App\Models\CityAlert::findOrFail($id);
        $alert->delete();
        return back()->with('success', 'City alert subscription removed successfully!');
    }

    // New Owner Registration by Admin
    public function createOwner()
    {
        return view('admin.members.form', ['member' => new User(), 'memberRole' => 'owner']);
    }

    public function storeOwner(Request $request)
    {
        $owner = $this->createMember($request, 'owner');
        return redirect()->route('admin.owners.detail', $owner)->with('success', 'Owner account created successfully.');
    }

    public function editOwner(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        return view('admin.members.form', ['member' => $owner, 'memberRole' => 'owner']);
    }

    public function updateOwner(Request $request, User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $this->updateMember($request, $owner);
        return redirect()->route('admin.owners.detail', $owner)->with('success', 'Owner account updated successfully.');
    }

    public function destroyOwner(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->delete();
        return redirect()->route('admin.owners')->with('success', 'Owner account deleted. Listings are retained and the account can be restored.');
    }

    private function createMember(Request $request, string $role): User
    {
        $data = $this->validateMember($request);
        // Members authenticate with an email OTP. Keep an unusable random value only
        // because the legacy users table still requires a non-null password column.
        $data['password'] = Hash::make(Str::random(64));
        $data['role'] = $role;
        $data['is_verified'] = $data['verification_status'] === 'verified';
        $data['verified_at'] = $data['is_verified'] ? now() : null;
        $data['email_verified_at'] = $request->boolean('email_verified') ? now() : null;
        $data['is_blocked'] = $request->boolean('is_blocked');
        $data['block_reason'] = $data['is_blocked'] ? ($data['block_reason'] ?: 'Blocked by administrator') : null;
        unset($data['email_verified']);

        return User::create($data);
    }

    private function updateMember(Request $request, User $member): void
    {
        $data = $this->validateMember($request, $member);
        $data['is_verified'] = $data['verification_status'] === 'verified';
        $data['verified_at'] = $data['is_verified'] ? ($member->verified_at ?: now()) : null;
        $data['email_verified_at'] = $request->boolean('email_verified') ? ($member->email_verified_at ?: now()) : null;
        $data['is_blocked'] = $request->boolean('is_blocked');
        $data['block_reason'] = $data['is_blocked'] ? ($data['block_reason'] ?: 'Blocked by administrator') : null;
        unset($data['email_verified']);
        $member->update($data);
    }

    private function validateMember(Request $request, ?User $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($member?->id)],
            'verification_status' => ['required', Rule::in(['pending', 'under_review', 'verified', 'rejected'])],
            'wallet_balance' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'free_unlocks' => ['required', 'integer', 'min:0', 'max:100000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'block_reason' => ['nullable', 'string', 'max:255'],
            'is_blocked' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);
    }

    // Room Management by Admin
    public function createRoom()
    {
        $owners = User::where('role', 'owner')->get();
        return view('admin.rooms.create', compact('owners'));
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'room_type' => ['required', Rule::in(RoomOption::validIdsFor('room_type'))],
            'amenities' => 'nullable|array',
            'landmarks' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'photos' => 'required|array|min:1|max:5',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'listing_fee_paid' => 'nullable|boolean',
        ]);

        $data['user_id'] = $request->owner_id;
        $data['listing_status'] = 'approved';
        $data['listing_fee_paid'] = $request->has('listing_fee_paid') ? true : true; // Default true for admin
        $data['is_featured'] = $request->has('is_featured');
        $data['status'] = 'active';

        // Convert empty latitude/longitude strings to null
        if (isset($data['latitude']) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('rooms', 'public');
            }
            $data['photos'] = $photos;
            $data['photo'] = $photos[0];
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('rooms/videos', 'public');
        }

        $data = $this->mapRoomOptionData($data);

        Room::create($data);

        return redirect()->route('admin.all-rooms')->with('success', 'Room created successfully by Admin!');
    }

    public function editRoom(Room $room)
    {
        $owners = User::where('role', 'owner')->get();
        return view('admin.rooms.edit', compact('room', 'owners'));
    }

    public function updateRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'room_type' => ['required', Rule::in(RoomOption::validIdsFor('room_type'))],
            'amenities' => 'nullable|array',
            'landmarks' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'photos' => 'nullable|array|max:5',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'listing_fee_paid' => 'nullable|boolean',
            'listing_status' => 'nullable|in:pending,approved,rejected',
            'status' => 'nullable|in:active,booked',
        ]);

        $data['user_id'] = $request->owner_id;
        $data['is_featured'] = $request->has('is_featured');
        $data['listing_fee_paid'] = $request->has('listing_fee_paid');
        $data['listing_status'] = $request->listing_status ?? $room->listing_status;
        $data['status'] = $request->status ?? $room->status;

        // Convert empty latitude/longitude strings to null
        if (isset($data['latitude']) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }

        $oldPhotosToDelete = [];
        if ($request->hasFile('photos')) {
            // Store the replacement first. Old media is removed only after the DB update succeeds.
            $oldPhotosToDelete = is_array($room->photos) ? $room->photos : (json_decode($room->photos ?: '[]', true) ?: []);
            if ($room->photo && !in_array($room->photo, $oldPhotosToDelete, true)) $oldPhotosToDelete[] = $room->photo;
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('rooms', 'public');
            }
            $data['photos'] = $photos;
            $data['photo'] = $photos[0];
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            // Delete old video
            if ($room->video) {
                Storage::disk('public')->delete($room->video);
            }
            $data['video'] = $request->file('video')->store('rooms/videos', 'public');
        }

        $data = $this->mapRoomOptionData($data);

        $room->update($data);

        foreach ($oldPhotosToDelete as $oldPhoto) {
            if ($oldPhoto && !str_starts_with($oldPhoto, 'http') && !in_array($oldPhoto, $data['photos'] ?? [], true)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        return redirect()->route('admin.rooms.edit', $room->fresh())->with('success', 'Room details and media updated successfully.');
    }

    public function showRoom(Room $room)
    {
        $isUnlocked = true; 
        $isOwner = true; 
        $subscriptionRemaining = 0;
        $room->load('owner');
        
        return view('rooms.show', compact('room', 'isUnlocked', 'isOwner', 'subscriptionRemaining'));
    }

    public function paymentsindex(Request $request)
    {
        $query = Payment::with('user');
        if ($request->filled('search')) {
            $term=trim($request->search);
            $query->where(fn($q)=>$q->where('transaction_id','like',"%{$term}%")
                ->orWhere('gateway_order_id','like',"%{$term}%")->orWhere('reference_id','like',"%{$term}%")
                ->orWhereHas('user',fn($u)=>$u->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('type')) $query->where('type',$request->type);
        if ($request->filled('gateway')) $query->where('gateway',$request->gateway);
        if ($request->filled('from')) $query->whereDate('created_at','>=',$request->date('from'));
        if ($request->filled('to')) $query->whereDate('created_at','<=',$request->date('to'));
        if ($request->filled('min_amount')) $query->where('amount','>=',(float)$request->min_amount);
        if ($request->filled('max_amount')) $query->where('amount','<=',(float)$request->max_amount);
        $filtered=(clone $query);
        $paymentStats=[
            'total'=>$filtered->count(),
            'collected'=>(clone $filtered)->where('status','completed')->sum('amount'),
            'completed'=>(clone $filtered)->where('status','completed')->count(),
            'pending'=>(clone $filtered)->where('status','pending')->count(),
            'failed'=>(clone $filtered)->where('status','failed')->count(),
        ];
        $payments=$query->latest()->paginate(20)->withQueryString();
        $types=Payment::whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $gateways=Payment::whereNotNull('gateway')->distinct()->orderBy('gateway')->pluck('gateway');
        return view('admin.payments.index',compact('payments','paymentStats','types','gateways'));
    }

    public function contactMessages()
    {
        $messages = \App\Models\ContactMessage::latest()->paginate(15);
        return view('admin.contact-messages.index', compact('messages'));
    }

    public function deleteContactMessage($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->delete();
        return back()->with('success', 'Contact message deleted successfully!');
    }

    public function markMessageAsRead($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);
        return back()->with('success', 'Message marked as read.');
    }
}

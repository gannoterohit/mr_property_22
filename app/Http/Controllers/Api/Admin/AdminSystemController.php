<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Complaint;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\SearchLog;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSystemController extends BaseApiController
{
    private const KEYS=['maintenance_mode','maintenance_title','maintenance_message','maintenance_reopening_at','registration_enabled','new_listings_enabled','payments_enabled','owner_panel_enabled','user_panel_enabled'];
    public function maintenance(Request $request){$this->allow($request,'settings.manage');return $this->sendSuccess(collect(self::KEYS)->mapWithKeys(fn($k)=>[$k=>Setting::get($k)])->all());}
    public function updateMaintenance(Request $request){$this->allow($request,'settings.manage');$d=$request->validate(['maintenance_mode'=>'required|boolean','maintenance_title'=>'nullable|string|max:120','maintenance_message'=>'nullable|string|max:1000','maintenance_reopening_at'=>'nullable|date','registration_enabled'=>'required|boolean','new_listings_enabled'=>'required|boolean','payments_enabled'=>'required|boolean','owner_panel_enabled'=>'required|boolean','user_panel_enabled'=>'required|boolean']);foreach($d as $k=>$v)Setting::updateOrCreate(['key'=>$k],['value'=>is_bool($v)?($v?'1':'0'):$v]);return $this->sendSuccess($d,'Platform availability updated');}
    public function reports(Request $request){$this->allow($request,'reports.view');$from=$request->date('from')?->startOfDay()??now()->subDays(29)->startOfDay();$to=$request->date('to')?->endOfDay()??now()->endOfDay();$payments=Payment::whereBetween('created_at',[$from,$to]);return $this->sendSuccess(['range'=>['from'=>$from,'to'=>$to],'revenue'=>['total'=>(float)(clone $payments)->where('status','completed')->sum('amount'),'listing'=>(float)(clone $payments)->where('status','completed')->where('type','listing')->sum('amount'),'featured'=>(float)(clone $payments)->where('status','completed')->where('type','featured')->sum('amount'),'unlock'=>(float)(clone $payments)->where('status','completed')->where('type','unlock')->sum('amount'),'subscription'=>(float)(clone $payments)->where('status','completed')->where('type','subscription')->sum('amount'),'failed'=>(float)(clone $payments)->where('status','failed')->sum('amount')],'growth'=>['users'=>User::where('role','user')->whereBetween('created_at',[$from,$to])->count(),'owners'=>User::where('role','owner')->whereBetween('created_at',[$from,$to])->count(),'listings'=>Room::whereBetween('created_at',[$from,$to])->count(),'unlocks'=>Enquiry::where('unlocked',true)->whereBetween('unlocked_at',[$from,$to])->count(),'subscriptions'=>Subscription::whereBetween('created_at',[$from,$to])->count()],'support'=>['complaints'=>Complaint::whereBetween('created_at',[$from,$to])->count(),'resolved'=>Complaint::whereBetween('closed_at',[$from,$to])->count(),'average_resolution_hours'=>(float)(Complaint::whereNotNull('closed_at')->whereBetween('closed_at',[$from,$to])->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_hours')->value('avg_hours')??0)],'city_demand'=>SearchLog::whereBetween('created_at',[$from,$to])->select('city',DB::raw('count(*) as searches'))->groupBy('city')->orderByDesc('searches')->limit(10)->get()]);}
    public function bulkRooms(Request $request){$this->allow($request,'listings.manage');$d=$request->validate(['room_ids'=>'required|array|min:1|max:100','room_ids.*'=>'integer|exists:rooms,id','action'=>['required',Rule::in(['approve','suspend','activate','reject','delete'])],'rejection_reason'=>'nullable|string|max:1000']);if($d['action']==='reject'&&!$request->filled('rejection_reason'))return $this->sendError('Rejection reason is required.',[],422);$q=Room::whereIn('id',$d['room_ids']);$count=$q->count();match($d['action']){'approve'=>$q->update(['listing_status'=>'approved','status'=>'active','rejection_reason'=>null]),'suspend'=>$q->update(['status'=>'inactive']),'activate'=>$q->update(['status'=>'active']),'reject'=>$q->update(['listing_status'=>'rejected','rejection_reason'=>$d['rejection_reason']]),'delete'=>$q->get()->each->delete()};return $this->sendSuccess(['affected'=>$count],'Bulk action completed');}
    public function deleteSearchLog(Request $request,SearchLog $searchLog){$this->allow($request,'reports.view');$searchLog->delete();return $this->sendSuccess([],'Search log deleted');}
    public function deleteSearchLogs(Request $request){$this->allow($request,'reports.view');$d=$request->validate(['all'=>'nullable|boolean','from'=>'nullable|date|required_unless:all,true','to'=>'nullable|date|after_or_equal:from|required_unless:all,true']);$q=SearchLog::query();if(!$request->boolean('all'))$q->whereBetween('created_at',[$request->date('from')->startOfDay(),$request->date('to')->endOfDay()]);$count=$q->delete();return $this->sendSuccess(['deleted'=>$count],'Search logs deleted');}
    private function allow(Request $request,string $permission):void{abort_unless($request->user()->hasAdminPermission($permission),403,'You do not have permission for this admin operation.');}
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AdminActivityLog;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminAccessController extends BaseApiController
{
    public function catalog(Request $request){$this->allow($request,'staff.manage');return $this->sendSuccess(config('admin_permissions.catalog'));}
    public function staff(Request $request){$this->allow($request,'staff.manage');return $this->sendSuccess(User::where('role','admin')->with('adminRole')->latest()->paginate($this->limit($request)));}
    public function staffStore(Request $request){$this->allow($request,'staff.manage');$d=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|unique:users,email','phone'=>'nullable|string|max:20','password'=>'required|string|min:8|confirmed','admin_role_id'=>'required|exists:admin_roles,id']);$d+=['role'=>'admin','is_verified'=>true,'is_staff_active'=>true,'email_verified_at'=>now()];$d['password']=Hash::make($d['password']);return $this->sendSuccess(User::create($d)->load('adminRole'),'Staff account created',201);}
    public function staffUpdate(Request $request, User $staff){$this->allow($request,'staff.manage');abort_unless($staff->role==='admin',404);$d=$request->validate(['name'=>'required|string|max:100','email'=>['required','email',Rule::unique('users','email')->ignore($staff->id)],'phone'=>'nullable|string|max:20','admin_role_id'=>'required|exists:admin_roles,id','password'=>'nullable|string|min:8|confirmed']);if(!empty($d['password']))$d['password']=Hash::make($d['password']);else unset($d['password']);$staff->update($d);return $this->sendSuccess($staff->fresh()->load('adminRole'),'Staff account updated');}
    public function staffToggle(Request $request, User $staff){$this->allow($request,'staff.manage');abort_unless($staff->role==='admin',404);if($staff->id===$request->user()->id)return $this->sendError('You cannot disable your own account.',[],422);$staff->update(['is_staff_active'=>!$staff->is_staff_active]);return $this->sendSuccess($staff,'Staff access updated');}
    public function roles(Request $request){$this->allow($request,'staff.manage');return $this->sendSuccess(['roles'=>AdminRole::withCount('staff')->orderBy('name')->get(),'catalog'=>config('admin_permissions.catalog')]);}
    public function roleStore(Request $request){$this->allow($request,'staff.manage');$d=$this->roleData($request);$d['slug']=Str::slug($d['name'],'_');$d['is_system']=false;return $this->sendSuccess(AdminRole::create($d),'Role created',201);}
    public function roleUpdate(Request $request, AdminRole $role){$this->allow($request,'staff.manage');if($role->slug==='super_admin')return $this->sendError('Super Admin permissions cannot be reduced.',[],422);$role->update($this->roleData($request,$role));return $this->sendSuccess($role->fresh(),'Role updated');}
    public function activityLogs(Request $request){$this->allow($request,'activity.view');$q=AdminActivityLog::with('actor:id,name,email')->when($request->filled('actor'),fn($x)=>$x->where('actor_id',$request->actor))->when($request->filled('search'),fn($x)=>$x->where(fn($y)=>$y->where('description','like','%'.$request->search.'%')->orWhere('route_name','like','%'.$request->search.'%')));return $this->sendSuccess(['stats'=>['total'=>AdminActivityLog::count(),'today'=>AdminActivityLog::whereDate('created_at',today())->count(),'active_staff_30d'=>AdminActivityLog::where('created_at','>=',now()->subDays(30))->distinct('actor_id')->count('actor_id')],'logs'=>$q->latest()->paginate($this->limit($request))]);}
    public function ownerVerification(Request $request, User $owner){$this->allow($request,'people.manage');abort_unless($owner->role==='owner',404);$d=$request->validate(['verification_status'=>'required|in:pending,verified,rejected','admin_notes'=>'nullable|string|max:5000']);$d['is_verified']=$d['verification_status']==='verified';$d['verified_at']=$d['is_verified']?now():null;$owner->update($d);return $this->sendSuccess($owner->fresh(),'Owner verification updated');}
    private function roleData(Request $request,?AdminRole $role=null):array{$catalog=array_keys(config('admin_permissions.catalog'));$d=$request->validate(['name'=>['required','string','max:80',Rule::unique('admin_roles','name')->ignore($role?->id)],'description'=>'nullable|string|max:255','permissions'=>'array','permissions.*'=>Rule::in($catalog)]);$p=$d['permissions']??[];foreach(['listings','people','support','finance','content','reports'] as $m)if(in_array("$m.manage",$p,true)&&!in_array("$m.view",$p,true))$p[]="$m.view";if($p&&!in_array('dashboard.view',$p,true))$p[]='dashboard.view';$d['permissions']=array_values(array_unique($p));return $d;}
    private function allow(Request $request,string $permission):void{abort_unless($request->user()->hasAdminPermission($permission),403,'You do not have permission for this admin operation.');}
    private function limit(Request $request):int{return max(1,min(50,$request->integer('limit',20)));}
}

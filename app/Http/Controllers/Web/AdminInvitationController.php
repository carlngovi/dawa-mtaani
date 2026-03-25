<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminInvitationController extends Controller
{
    public function index()
    {
        if (!request()->user()->hasRole(['network_admin'])) return redirect('/admin/dashboard');
        $invitations = DB::table('user_invitations as i')->join('users as u','i.invited_by','=','u.id')->leftJoin('facilities as f','i.facility_id','=','f.id')->select(['i.*','u.name as invited_by_name','f.facility_name'])->orderByDesc('i.created_at')->paginate(30);
        $roles = ['network_admin'=>'Network Admin','network_field_agent'=>'Field Agent','retail_facility'=>'Retail Facility','wholesale_facility'=>'Wholesale Facility'];
        $facilities = DB::table('facilities')->whereNull('deleted_at')->where('facility_status','ACTIVE')->orderBy('facility_name')->get(['id','facility_name','ppb_facility_type']);
        return view('admin.invitations', compact('invitations','roles','facilities'));
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasRole(['network_admin'])) return redirect('/admin/dashboard');
        $request->validate(['email'=>'required|email|unique:users,email|unique:user_invitations,email','name'=>'required|string|max:100','intended_role'=>'required|string','facility_id'=>'nullable|exists:facilities,id']);
        $token = Str::random(64);
        DB::table('user_invitations')->insert(['email'=>strtolower($request->email),'token'=>$token,'name'=>$request->name,'intended_role'=>$request->intended_role,'facility_id'=>$request->facility_id,'invited_by'=>$request->user()->id,'expires_at'=>Carbon::now('UTC')->addDays(7),'created_at'=>Carbon::now('UTC'),'updated_at'=>Carbon::now('UTC')]);
        return redirect('/admin/invitations')->with('success','Invitation created. Share this link: '.url('/register/accept/'.$token));
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasRole(['network_admin'])) return redirect('/admin/dashboard');
        DB::table('user_invitations')->where('id',$id)->delete();
        return redirect('/admin/invitations')->with('success','Invitation revoked.');
    }

    public function showAccept($token)
    {
        $invitation = DB::table('user_invitations')->where('token',$token)->whereNull('accepted_at')->where('expires_at','>',Carbon::now('UTC'))->first();
        if (!$invitation) return redirect('/login')->withErrors(['email'=>'This invitation link is invalid or has expired.']);
        return view('auth.accept-invitation', compact('invitation','token'));
    }

    public function acceptStore(Request $request, $token)
    {
        $invitation = DB::table('user_invitations')->where('token',$token)->whereNull('accepted_at')->where('expires_at','>',Carbon::now('UTC'))->first();
        if (!$invitation) return redirect('/login')->withErrors(['email'=>'This invitation link is invalid or has expired.']);
        $request->validate(['password'=>'required|string|min:8|confirmed']);
        $user = User::create(['name'=>$invitation->name,'email'=>$invitation->email,'password'=>Hash::make($request->password),'facility_id'=>$invitation->facility_id,'is_active'=>true]);
        $user->assignRole($invitation->intended_role);
        DB::table('user_invitations')->where('id',$invitation->id)->update(['accepted_at'=>Carbon::now('UTC'),'updated_at'=>Carbon::now('UTC')]);
        auth()->login($user);
        if ($user->hasRole(['network_admin','network_field_agent'])) return redirect('/admin/dashboard');
        if ($user->hasRole('wholesale_facility')) return redirect('/wholesale/orders');
        return redirect('/retail/dashboard');
    }
}

<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
class DashboardController extends Controller {
    public function __invoke(Request $request) {
        $user=$request->user();
        if ($user->role==='platform_super_admin') {
            return response()->json(['companies'=>Company::count(),'active_companies'=>Company::where('status','active')->count(),'suspended_companies'=>Company::where('status','suspended')->count(),'users'=>User::count(),'tickets'=>Ticket::count()]);
        }
        $q=Ticket::where('company_id',$user->company_id);
        if ($user->role==='customer') $q->where('customer_id',$user->id);
        if ($user->role==='support_agent') $q->where(fn($x)=>$x->where('assigned_to',$user->id)->orWhereNull('assigned_to'));
        return response()->json([
            'total'=>$q->count(),
            'open'=>(clone $q)->where('status','open')->count(),
            'in_progress'=>(clone $q)->where('status','in_progress')->count(),
            'pending'=>(clone $q)->where('status','pending')->count(),
            'resolved'=>(clone $q)->whereIn('status',['resolved','closed'])->count(),
            'urgent'=>(clone $q)->where('priority','urgent')->count(),
        ]);
    }
}

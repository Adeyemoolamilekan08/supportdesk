<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TicketController extends Controller {
    private function scoped(Request $request) {
        $user = $request->user();
        $query = Ticket::where('company_id', $user->company_id);
        if ($user->role === 'customer') $query->where('customer_id', $user->id);
        if ($user->role === 'support_agent') $query->where(fn($q) => $q->where('assigned_to', $user->id)->orWhereNull('assigned_to'));
        return $query;
    }
    public function index(Request $request) {
        $data = $request->validate([
            'search'=>'nullable|string|max:120',
            'status'=>['nullable',Rule::in(['open','in_progress','pending','resolved','closed'])],
            'priority'=>['nullable',Rule::in(['low','medium','high','urgent'])],
            'per_page'=>'nullable|integer|min:1|max:100',
        ]);
        $q = $this->scoped($request)->with(['customer:id,name,email','assignee:id,name'])->latest();
        if (!empty($data['search'])) $q->where(fn($x)=>$x->where('subject','like','%'.$data['search'].'%')->orWhere('ticket_number','like','%'.$data['search'].'%'));
        if (!empty($data['status'])) $q->where('status',$data['status']);
        if (!empty($data['priority'])) $q->where('priority',$data['priority']);
        return response()->json($q->paginate($data['per_page'] ?? 15));
    }
    public function store(Request $request) {
        abort_unless($request->user()->company_id, 403, 'A company account is required.');
        $data = $request->validate(['subject'=>'required|string|max:180','description'=>'required|string|max:10000','priority'=>['nullable',Rule::in(['low','medium','high','urgent'])],'category'=>'nullable|string|max:100']);
        $ticket = DB::transaction(function () use ($request,$data) {
            $ticket = Ticket::create([
                'company_id'=>$request->user()->company_id,
                'customer_id'=>$request->user()->id,
                'subject'=>$data['subject'],'description'=>$data['description'],
                'ticket_number'=>'SD-'.strtoupper(Str::random(8)),
                'priority'=>$data['priority'] ?? 'medium','category'=>$data['category'] ?? 'General','status'=>'open'
            ]);
            TicketActivity::create(['ticket_id'=>$ticket->id,'user_id'=>$request->user()->id,'event'=>'created','description'=>'Ticket created']);
            return $ticket;
        });
        return response()->json(['ticket'=>$ticket->load(['customer:id,name,email','assignee:id,name'])],201);
    }
    public function show(Request $request, int $ticket) {
        $item = $this->scoped($request)->with(['customer:id,name,email','assignee:id,name','activities.user:id,name,role','replies'=>function($q) use ($request) {
            if ($request->user()->role === 'customer') $q->where('is_internal',false);
            $q->with('user:id,name,role')->oldest();
        }])->findOrFail($ticket);
        return response()->json(['ticket'=>$item]);
    }
    public function update(Request $request, int $ticket) {
        $item = $this->scoped($request)->findOrFail($ticket);
        abort_if($request->user()->role === 'customer',403,'Customers cannot edit ticket workflow fields.');
        $data = $request->validate([
            'status'=>['sometimes',Rule::in(['open','in_progress','pending','resolved','closed'])],
            'priority'=>['sometimes',Rule::in(['low','medium','high','urgent'])],
            'assigned_to'=>['sometimes','nullable','integer','exists:users,id'],
            'category'=>'sometimes|string|max:100',
        ]);
        if (isset($data['assigned_to'])) {
            $assignee = \App\Models\User::where('id',$data['assigned_to'])->where('company_id',$request->user()->company_id)->whereIn('role',['support_agent','company_admin'])->first();
            abort_unless($assignee,422,'Assignee must belong to your company and be a support team member.');
        }
        DB::transaction(function() use ($item,$data,$request) {
            $before = $item->only(array_keys($data));
            $item->update($data);
            foreach ($data as $field=>$value) {
                if (($before[$field] ?? null) != $value) TicketActivity::create(['ticket_id'=>$item->id,'user_id'=>$request->user()->id,'event'=>$field.'_changed','description'=>ucfirst(str_replace('_',' ',$field)).' updated','metadata'=>['from'=>$before[$field] ?? null,'to'=>$value]]);
            }
        });
        return response()->json(['ticket'=>$item->fresh()->load(['customer:id,name,email','assignee:id,name'])]);
    }
    public function reply(Request $request, int $ticket) {
        $item = $this->scoped($request)->findOrFail($ticket);
        $data = $request->validate(['body'=>'required|string|max:10000','is_internal'=>'sometimes|boolean']);
        $internal = (bool)($data['is_internal'] ?? false);
        abort_if($internal && !in_array($request->user()->role,['company_admin','support_agent','platform_super_admin']),403,'Only support staff can add internal notes.');
        DB::transaction(function() use ($item,$data,$internal,$request) {
            $item->replies()->create(['user_id'=>$request->user()->id,'body'=>$data['body'],'is_internal'=>$internal]);
            $item->update(['last_response_at'=>now()]);
            TicketActivity::create(['ticket_id'=>$item->id,'user_id'=>$request->user()->id,'event'=>$internal?'internal_note':'reply_added','description'=>$internal?'Internal note added':'Reply added']);
        });
        return response()->json(['message'=>'Reply added.','ticket'=>$item->fresh()]);
    }
}

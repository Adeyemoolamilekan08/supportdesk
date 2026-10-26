<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Ticket extends Model {
    protected $fillable = ['company_id','customer_id','assigned_to','subject','description','ticket_number','category','priority','status','due_at','last_response_at'];
    protected function casts(): array { return ['due_at'=>'datetime','last_response_at'=>'datetime']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies(): HasMany { return $this->hasMany(TicketReply::class); }
    public function activities(): HasMany { return $this->hasMany(TicketActivity::class); }
}

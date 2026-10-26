<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = ['company_id','name','email','password','role','status'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function createdTickets(): HasMany { return $this->hasMany(Ticket::class, 'customer_id'); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketActivity extends Model {
    protected $fillable = ['ticket_id','user_id','event','description','metadata'];
    protected function casts(): array { return ['metadata'=>'array']; }
}

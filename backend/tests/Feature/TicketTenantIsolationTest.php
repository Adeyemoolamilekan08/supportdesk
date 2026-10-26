<?php
namespace Tests\Feature;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class TicketTenantIsolationTest extends TestCase {
 use RefreshDatabase;
 public function test_user_cannot_read_ticket_from_another_company(): void {
  $a=Company::create(['name'=>'A','slug'=>'company-a','status'=>'active']);
  $b=Company::create(['name'=>'B','slug'=>'company-b','status'=>'active']);
  $ua=User::create(['company_id'=>$a->id,'name'=>'A User','email'=>'a@example.test','password'=>'password','role'=>'company_admin','status'=>'active']);
  $ub=User::create(['company_id'=>$b->id,'name'=>'B User','email'=>'b@example.test','password'=>'password','role'=>'company_admin','status'=>'active']);
  $ticket=Ticket::create(['company_id'=>$b->id,'customer_id'=>$ub->id,'ticket_number'=>'SD-OTHER01','subject'=>'Private','description'=>'Secret','priority'=>'high','status'=>'open']);
  $this->actingAs($ua)->getJson('/api/v1/tickets/'.$ticket->id)->assertNotFound();
 }
 public function test_customer_only_sees_own_tickets(): void {
  $c=Company::create(['name'=>'A','slug'=>'company-a','status'=>'active']);
  $one=User::create(['company_id'=>$c->id,'name'=>'One','email'=>'one@example.test','password'=>'password','role'=>'customer','status'=>'active']);
  $two=User::create(['company_id'=>$c->id,'name'=>'Two','email'=>'two@example.test','password'=>'password','role'=>'customer','status'=>'active']);
  Ticket::create(['company_id'=>$c->id,'customer_id'=>$two->id,'ticket_number'=>'SD-OTHER02','subject'=>'Other','description'=>'Secret','priority'=>'medium','status'=>'open']);
  $this->actingAs($one)->getJson('/api/v1/tickets')->assertOk()->assertJsonPath('total',0);
 }
}

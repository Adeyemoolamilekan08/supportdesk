<?php
namespace Database\Seeders;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  $company=Company::firstOrCreate(['slug'=>'acme-support'],['name'=>'Acme Support','status'=>'active','settings'=>[]]);
  User::firstOrCreate(['email'=>'admin@acme.test'],['company_id'=>$company->id,'name'=>'Alex Morgan','password'=>'password','role'=>'company_admin','status'=>'active']);
  User::firstOrCreate(['email'=>'agent@acme.test'],['company_id'=>$company->id,'name'=>'Sam Taylor','password'=>'password','role'=>'support_agent','status'=>'active']);
  User::firstOrCreate(['email'=>'customer@acme.test'],['company_id'=>$company->id,'name'=>'Jamie Customer','password'=>'password','role'=>'customer','status'=>'active']);
 }
}

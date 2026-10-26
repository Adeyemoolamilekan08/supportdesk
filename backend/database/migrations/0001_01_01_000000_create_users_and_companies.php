<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('companies', function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->string('status')->default('active')->index();$t->json('settings')->nullable();$t->timestamps();});
  Schema::create('users', function(Blueprint $t){$t->id();$t->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();$t->string('name');$t->string('email')->unique();$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->string('role')->default('customer')->index();$t->string('status')->default('active')->index();$t->rememberToken();$t->timestamps();});
  Schema::create('personal_access_tokens', function(Blueprint $t){$t->id();$t->morphs('tokenable');$t->string('name');$t->string('token',64)->unique();$t->text('abilities')->nullable();$t->timestamp('last_used_at')->nullable();$t->timestamp('expires_at')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('personal_access_tokens');Schema::dropIfExists('users');Schema::dropIfExists('companies');}
};

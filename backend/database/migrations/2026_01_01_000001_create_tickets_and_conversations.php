<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('tickets',function(Blueprint $t){$t->id();$t->foreignId('company_id')->constrained()->cascadeOnDelete();$t->foreignId('customer_id')->constrained('users')->restrictOnDelete();$t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();$t->string('ticket_number')->unique();$t->string('subject');$t->text('description');$t->string('category')->default('General');$t->string('priority')->default('medium')->index();$t->string('status')->default('open')->index();$t->timestamp('due_at')->nullable()->index();$t->timestamp('last_response_at')->nullable();$t->timestamps();$t->index(['company_id','status','created_at']);});
  Schema::create('ticket_replies',function(Blueprint $t){$t->id();$t->foreignId('ticket_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->text('body');$t->boolean('is_internal')->default(false)->index();$t->timestamps();});
  Schema::create('ticket_activities',function(Blueprint $t){$t->id();$t->foreignId('ticket_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('event');$t->string('description');$t->json('metadata')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('ticket_activities');Schema::dropIfExists('ticket_replies');Schema::dropIfExists('tickets');}
};

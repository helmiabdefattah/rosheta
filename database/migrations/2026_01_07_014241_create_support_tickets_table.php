<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticketable_type'); // 'App\Models\Laboratory' or 'App\Models\Nurse'
            $table->unsignedBigInteger('ticketable_id'); // laboratory_id or nurse_id
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('type', ['technical', 'billing', 'feature_request', 'complaint', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('admin_response')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['ticketable_type', 'ticketable_id']);
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

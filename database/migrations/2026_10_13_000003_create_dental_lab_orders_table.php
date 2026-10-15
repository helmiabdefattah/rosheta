<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A work order sent from a clinic to a dental lab. Belongs to one clinic and one
 * lab, optionally for one patient (material-only orders have none).
 *
 * Workflow status: sent → preparing → ready → received.
 *
 * teeth_mode = 'order' : the order-level `teeth` apply to every tooth item.
 * teeth_mode = 'item'  : each line item carries its own teeth instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dental_lab_id')->constrained('dental_labs')->cascadeOnDelete();
            // The patient (clients table); null for clinic-general / material orders.
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('status')->default('sent'); // sent | preparing | ready | received
            $table->string('teeth_mode')->default('order'); // order | item
            $table->json('teeth')->nullable(); // order-level Palmer codes (UR1…DL8)
            $table->text('notes')->nullable();
            // Set the first time the order reaches "received" — drives stock deduction.
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index(['dental_lab_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_lab_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments the PATIENT makes to the CLINIC against a lab order (clinic receivable
 * — distinct from dental_lab_payments, which is the clinic paying the lab). One
 * row per payment so the patient can settle in instalments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_patient_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_lab_order_id')->constrained('dental_lab_orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at');
            $table->string('method')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dental_lab_order_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_patient_payments');
    }
};

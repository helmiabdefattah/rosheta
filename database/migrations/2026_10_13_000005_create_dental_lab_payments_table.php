<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments from the clinic to the lab for an order. One row per payment so an
 * order can be settled in instalments; the order's payment status (unpaid /
 * partial / paid) is derived from the sum against the order total.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_lab_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_lab_order_id')->constrained('dental_lab_orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at');
            $table->string('method')->nullable(); // cash | transfer | cheque | …
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dental_lab_order_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_lab_payments');
    }
};

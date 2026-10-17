<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One payment made against an expense. An expense may be settled in several
 * instalments; the expense's status is recomputed from the sum of these rows.
 * Mirrors the patient-side `collections` table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at');
            $table->string('method')->nullable(); // cash | card | bank_transfer | …
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['expense_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};

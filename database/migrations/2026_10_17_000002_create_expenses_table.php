<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A clinic expense. `amount` is the net cost and `tax_amount` any VAT on top;
 * the gross (amount + tax) is what the expense_payments ledger settles. `status`
 * is derived from that ledger. `source` (polymorphic, nullable) lets later
 * phases attach an expense to what caused it — e.g. a dental lab payout — so it
 * is kept here from the start even though Phase 1 only creates manual entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->date('expense_date');
            $table->string('status')->default('unpaid'); // unpaid | partial | paid
            $table->string('reference_no')->nullable();
            $table->nullableMorphs('source'); // e.g. a DentalLabPayment (later phases)
            $table->softDeletes();
            $table->timestamps();

            $table->index(['clinic_id', 'expense_date']);
            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

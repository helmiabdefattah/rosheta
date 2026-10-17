<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Templates for expenses that repeat (rent, salaries, subscriptions). The
 * generator creates a real expense each time `next_run_on` falls due, then
 * rolls it forward by `interval`. Generated expenses link back here via their
 * polymorphic `source`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->string('interval'); // weekly | monthly | quarterly | yearly
            $table->date('next_run_on');
            $table->date('last_generated_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
            $table->index(['is_active', 'next_run_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};

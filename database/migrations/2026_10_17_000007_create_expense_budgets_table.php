<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A monthly spending budget, either for one category or overall (null
 * category). `period_month` is the first day of the month it applies to. One
 * budget per clinic + category + month.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->cascadeOnDelete();
            $table->date('period_month'); // first of the month
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['clinic_id', 'expense_category_id', 'period_month'], 'expense_budgets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_budgets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-clinic expense categories (Rent, Salaries, Dental Lab Fees, …). Seeded
 * from a preset list the first time a clinic opens the expenses module, with
 * extra categories for dental clinics. `parent_id` leaves room for sub-
 * categories later; Phase 1 uses a flat list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('name');
            $table->string('color')->nullable();  // tailwind pill classes, optional
            $table->string('icon')->nullable();    // emoji, optional
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};

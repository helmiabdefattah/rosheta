<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A doctor's price list of chargeable extras (dressing, injection, stitches…)
 * offered on top of the visit fee. Scoped to the doctor rather than the clinic,
 * so the same list is reused at every clinic they work in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // One entry per name per doctor — re-adding an existing name updates
            // it rather than cluttering the dropdown with duplicates.
            $table->unique(['doctor_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billable_items');
    }
};

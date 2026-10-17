<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of dental lab work types, in three relationship levels:
 *   - patient         : whole-mouth work (complete/partial denture, retainer…)
 *   - tooth           : needs specific teeth (crown, veneer, bridge…)
 *   - clinic_general  : consumables/materials not tied to a patient (stone, wax…)
 *
 * No price is stored — labs charge differently, so price is entered per order.
 * clinic_id is NULL for the shared seeded catalog; a row scoped to a clinic is
 * that clinic's own custom item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('level'); // patient | tooth | clinic_general
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('default_unit'); // piece | arch | tooth | unit | gram | ml | box
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['clinic_id', 'level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_items');
    }
};

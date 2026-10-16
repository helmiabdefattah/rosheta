<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-visit growth measurements a paediatrician records on every visit: weight,
 * height/length, head circumference and temperature.
 *
 * Like the clinical-chart notes, these belong to the patient (client_id is the
 * anchor) so growth reads across every visit; appointment_id records which
 * visit it was taken during, and is unique so a visit holds one measurement row
 * that is edited in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_visit_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('weight_kg', 6, 3)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('head_circumference_cm', 6, 2)->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_visit_records');
    }
};

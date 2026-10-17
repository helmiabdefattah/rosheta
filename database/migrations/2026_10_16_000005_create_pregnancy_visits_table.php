<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Antenatal follow-up data captured at one visit: the mother's weight and blood
 * pressure, and the fetal data (fundal height, fetal heart rate, presentation,
 * estimated fetal weight, ultrasound notes).
 *
 * Anchored to the patient so the follow-up reads across the whole pregnancy;
 * appointment_id is unique so a visit holds one row edited in place. ga_days is
 * the gestational age snapshot at the visit, frozen at save time so history
 * stays stable even if the dating is later corrected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancy_visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedSmallInteger('ga_days')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();       // mother
            $table->string('blood_pressure', 12)->nullable();
            $table->decimal('fundal_height_cm', 5, 1)->nullable();
            $table->unsignedSmallInteger('fetal_heart_rate')->nullable();
            $table->string('presentation', 40)->nullable();
            $table->unsignedInteger('fetal_weight_g')->nullable(); // estimated fetal weight
            $table->unsignedTinyInteger('fetus_count')->nullable();
            $table->text('ultrasound')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_visits');
    }
};

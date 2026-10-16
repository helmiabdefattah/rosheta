<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A child's vaccination log. Belongs to the patient (client_id) so the whole
 * immunisation history shows on any visit; the clinic/doctor/appointment record
 * where and when each dose was entered. given_at is the date administered and
 * due_at the optional next-dose reminder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_vaccinations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('vaccine');
            $table->string('dose')->nullable();
            $table->date('given_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'given_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_vaccinations');
    }
};

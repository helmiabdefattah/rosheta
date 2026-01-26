<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unique(['clinic_id', 'doctor_id']);
        });

        // Seed pivot from existing clinic.doctor_id so existing clinics have their doctor in clinic_doctor
        $clinics = \DB::table('clinics')->whereNotNull('doctor_id')->get();
        foreach ($clinics as $c) {
            \DB::table('clinic_doctor')->insertOrIgnore([
                'clinic_id' => $c->id,
                'doctor_id' => $c->doctor_id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_doctor');
    }
};

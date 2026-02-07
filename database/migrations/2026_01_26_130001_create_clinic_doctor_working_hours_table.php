<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_doctor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->enum('day', [
                'saturday',
                'sunday',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
            ]);
            $table->time('from')->nullable();
            $table->time('to')->nullable();
            $table->boolean('is_closed')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'doctor_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_doctor_working_hours');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable prescription templates ("medical plans"): a titled set of medicines
 * the doctor can save from an examination and load into future prescriptions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();

            $table->index('doctor_id');
        });

        Schema::create('medical_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_plan_id')->constrained()->cascadeOnDelete();
            $table->string('medicine_name');
            $table->string('dose')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->string('instructions')->nullable();
            $table->timestamps();

            $table->index('medical_plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_plan_items');
        Schema::dropIfExists('medical_plans');
    }
};

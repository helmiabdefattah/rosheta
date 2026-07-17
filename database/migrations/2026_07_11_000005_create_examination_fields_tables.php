<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Doctor-defined custom examination fields (text / select / number /
 * percentage / file) and the per-appointment values captured for them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->enum('type', ['text', 'select', 'number', 'percentage', 'file'])->default('text');
            $table->text('options')->nullable(); // comma-separated choices for "select"
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['doctor_id', 'is_active']);
        });

        Schema::create('examination_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->timestamps();

            $table->unique(['appointment_id', 'examination_field_id'], 'exam_value_appt_field_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_field_values');
        Schema::dropIfExists('examination_fields');
    }
};

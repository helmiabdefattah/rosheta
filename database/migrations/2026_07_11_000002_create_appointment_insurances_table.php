<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-visit insurance split (a "claim"): how much the patient pays out of
 * pocket and how much is claimed from the insurance company for one appointment.
 * The sum of insurance_amount across visits is a company's "total claimed".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_company_id')->constrained();
            $table->decimal('patient_amount', 10, 2)->default(0);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('insurance_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_insurances');
    }
};

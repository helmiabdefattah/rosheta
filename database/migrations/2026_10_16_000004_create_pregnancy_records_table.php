<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The current pregnancy a woman is being followed for. One row per patient
 * (client_id unique), edited in place — the doctor sets the dating once and
 * every visit recomputes today's gestational age from it.
 *
 * Dating is anchored two ways:
 *   - 'lmp'    : lmp_date is day 0 of gestation.
 *   - 'manual' : the doctor states a gestational age (ga_weeks + ga_days) as it
 *                stood on ga_assessed_on (e.g. from a dating scan).
 * Either way the age "today" is the anchor age plus the days elapsed since the
 * anchor date; the expected delivery date follows from the same anchor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancy_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            $table->string('dating_method', 16)->default('lmp'); // lmp | manual
            $table->date('lmp_date')->nullable();
            $table->date('ga_assessed_on')->nullable();           // reference date for a manual age
            $table->unsignedSmallInteger('ga_weeks')->nullable(); // manual age at the reference date
            $table->unsignedTinyInteger('ga_days')->nullable();

            $table->unsignedSmallInteger('gravida')->nullable();  // pregnancies
            $table->unsignedSmallInteger('para')->nullable();     // births
            $table->string('blood_group', 8)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_records');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The clinic an assistant works at. users.doctor_id says *who* they assist;
 * this says *where*, which is what the per-clinic assistant limit counts and
 * what pins the assistant's workspace to a single clinic. Null for existing
 * assistants (they keep falling back to the doctor's first clinic) and for
 * every non-assistant user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'clinic_id')) {
                $table->foreignId('clinic_id')->nullable()->after('doctor_id')
                    ->constrained('clinics')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'clinic_id')) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            }
        });
    }
};

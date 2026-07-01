<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clinic system reuses `clients` as the patient identity. Add the clinical
 * profile fields the clinic (design) system needs. All nullable so existing
 * rosheta client rows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('name');
            }
            if (! Schema::hasColumn('clients', 'dob')) {
                $table->date('dob')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('clients', 'national_id')) {
                $table->string('national_id', 50)->nullable()->after('dob');
            }
            if (! Schema::hasColumn('clients', 'blood_type')) {
                $table->string('blood_type', 10)->nullable()->after('national_id');
            }
            if (! Schema::hasColumn('clients', 'address')) {
                $table->text('address')->nullable()->after('blood_type');
            }
            if (! Schema::hasColumn('clients', 'allergies')) {
                $table->json('allergies')->nullable()->after('address');
            }
            if (! Schema::hasColumn('clients', 'chronic_diseases')) {
                $table->json('chronic_diseases')->nullable()->after('allergies');
            }
            if (! Schema::hasColumn('clients', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('chronic_diseases');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'dob', 'national_id', 'blood_type',
                'address', 'allergies', 'chronic_diseases', 'medical_history',
            ]);
        });
    }
};

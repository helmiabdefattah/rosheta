<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links clinics to a medical center and — importantly — makes clinics.doctor_id
 * nullable so a center can hold clinics that have no doctor assigned yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'medical_center_id')) {
                $table->foreignId('medical_center_id')->nullable()->after('user_id')
                    ->constrained('medical_centers')->nullOnDelete();
            }
        });

        // Make doctor_id nullable: drop the FK, relax the column, re-add the FK
        // (nullOnDelete instead of cascade so a deleted doctor just detaches).
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->change();
        });
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'medical_center_id')) {
                $table->dropForeign(['medical_center_id']);
                $table->dropColumn('medical_center_id');
            }
            $table->dropForeign(['doctor_id']);
        });
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable(false)->change();
        });
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
        });
    }
};

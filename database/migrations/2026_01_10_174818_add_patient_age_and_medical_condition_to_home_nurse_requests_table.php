<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('home_nurse_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('patient_age')->nullable()->after('medical_notes');
            $table->text('medical_condition')->nullable()->after('patient_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_nurse_requests', function (Blueprint $table) {
            $table->dropColumn(['patient_age', 'medical_condition']);
        });
    }
};

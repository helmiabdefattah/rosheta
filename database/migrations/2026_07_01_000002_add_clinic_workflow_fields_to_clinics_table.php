<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The clinic (design) system stores opening hours as a JSON map keyed by
 * weekday, plus an appointment duration and a flag controlling the "call next"
 * button on the public waiting-room display. Add those to the shared `clinics`
 * table. Rosheta's own clinic_working_hours table is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'opening_hours')) {
                $table->json('opening_hours')->nullable()->after('phone_number');
            }
            if (! Schema::hasColumn('clinics', 'appointment_duration')) {
                $table->unsignedSmallInteger('appointment_duration')->default(30)->after('opening_hours');
            }
            if (! Schema::hasColumn('clinics', 'display_show_next_button')) {
                $table->boolean('display_show_next_button')->default(true)->after('appointment_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['opening_hours', 'appointment_duration', 'display_show_next_button']);
        });
    }
};

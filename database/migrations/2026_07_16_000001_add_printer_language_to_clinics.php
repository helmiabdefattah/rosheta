<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Language the clinic's Bluetooth ticket printer prints in. Without it the
 * ticket is rendered in whatever locale happened to trigger the print (the
 * kiosk patient's, or the assistant's), so a clinic could get mixed-language
 * tickets. NULL means "follow the app locale".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'printer_language')) {
                $table->string('printer_language', 2)->nullable()->after('printer_connected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('printer_language');
        });
    }
};

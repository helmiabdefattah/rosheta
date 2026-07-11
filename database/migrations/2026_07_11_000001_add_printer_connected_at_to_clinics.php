<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether a staff mobile app with a Bluetooth ticket printer is
 * currently online for a clinic. The app sends a heartbeat; the kiosk uses a
 * recent timestamp to decide whether to auto-print (skip the browser ticket).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->timestamp('printer_connected_at')->nullable()->after('display_show_next_button');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('printer_connected_at');
        });
    }
};

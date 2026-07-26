<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether the clinic's Bluetooth ticket printer should include the QR code on
 * the printed patient paper (the one printed with the patient's queue number).
 * Defaults to true so existing clinics keep printing the QR they print today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'print_qr')) {
                $table->boolean('print_qr')->default(true)->after('printer_language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('print_qr');
        });
    }
};

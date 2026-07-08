<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flag controlling the self-service "check in" button on the public
 * waiting-room display, toggled from the assistant dashboard alongside the
 * existing "call next" button flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'display_show_kiosk_button')) {
                $table->boolean('display_show_kiosk_button')->default(true)->after('display_show_next_button');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('display_show_kiosk_button');
        });
    }
};

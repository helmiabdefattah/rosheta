<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An optional alternative (substitute) medicine the doctor allows the pharmacy
 * to dispense in place of the primary one. Shown next to the medicine on the
 * examine screen and on every printed/PDF/thermal prescription.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (! Schema::hasColumn('prescription_items', 'substitute_name')) {
                $table->string('substitute_name')->nullable()->after('medicine_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn('substitute_name');
        });
    }
};

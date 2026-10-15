<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the clinic charges the PATIENT for a lab order (set by the clinic, usually
 * above the lab's own price). Separate from the line-item prices, which are what
 * the lab charges the clinic. Null when the order isn't billed to a patient.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_orders', function (Blueprint $table) {
            $table->decimal('patient_price', 10, 2)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('dental_lab_orders', function (Blueprint $table) {
            $table->dropColumn('patient_price');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The clinic's operating fee (سعر التشغيل) added to the patient's account on top
 * of the patient price. The patient's total charge = patient_price + operating_fee.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_orders', function (Blueprint $table) {
            $table->decimal('operating_fee', 10, 2)->nullable()->after('patient_price');
        });
    }

    public function down(): void
    {
        Schema::table('dental_lab_orders', function (Blueprint $table) {
            $table->dropColumn('operating_fee');
        });
    }
};

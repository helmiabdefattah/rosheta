<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-line operating fee (سعر التشغيل). The line's patient price = line total
 * (qty × unit price) + operating fee; the order's patient charge is the sum.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_order_items', function (Blueprint $table) {
            $table->decimal('operating_fee', 10, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('dental_lab_order_items', function (Blueprint $table) {
            $table->dropColumn('operating_fee');
        });
    }
};

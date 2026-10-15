<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A received material line can be marked "used": the doctor consumed it on the
 * patient, so it leaves clinic stock. used_at stamps when that happened (null =
 * still in stock); the stock-out movement is linked to the order for reversal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_order_items', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->after('teeth');
        });
    }

    public function down(): void
    {
        Schema::table('dental_lab_order_items', function (Blueprint $table) {
            $table->dropColumn('used_at');
        });
    }
};

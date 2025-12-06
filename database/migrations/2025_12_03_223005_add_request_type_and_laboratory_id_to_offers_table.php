<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offer_lines', function (Blueprint $table) {
            // إضافة الأعمدة للدعم المزدوج
            if (!Schema::hasColumn('offer_lines', 'medical_test_id')) {
                $table->foreignId('medical_test_id')->nullable()->after('medicine_id');
            }

            if (!Schema::hasColumn('offer_lines', 'item_type')) {
                $table->string('item_type')->default('medicine')->after('id');
            }

            // جعل medicine_id nullable لأنه قد لا يكون موجوداً في حالة الفحوصات
            $table->foreignId('medicine_id')->nullable()->change();

            // إضافة فهارس
            $table->index('item_type');
            $table->index('medical_test_id');

            // إضافة المفاتيح الخارجية
            if (Schema::hasTable('medical_tests')) {
                $table->foreign('medical_test_id')->references('id')->on('medical_tests')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_lines', function (Blueprint $table) {
            $table->dropForeign(['medical_test_id']);
            $table->dropColumn(['medical_test_id', 'item_type']);
            $table->foreignId('medicine_id')->nullable(false)->change();
        });
    }
};

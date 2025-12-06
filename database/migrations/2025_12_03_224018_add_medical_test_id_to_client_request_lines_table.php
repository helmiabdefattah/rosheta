<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_request_lines', function (Blueprint $table) {
            // إضافة medical_test_id
            if (!Schema::hasColumn('client_request_lines', 'medical_test_id')) {
                $table->foreignId('medical_test_id')->nullable()->after('medicine_id');
            }

            // إضافة item_type
            if (!Schema::hasColumn('client_request_lines', 'item_type')) {
                $table->string('item_type')->default('medicine')->after('id');
            }

            // جعل medicine_id nullable
            $table->foreignId('medicine_id')->nullable()->change();

            // إضافة المفتاح الخارجي
            if (Schema::hasTable('medical_tests')) {
                $table->foreign('medical_test_id')->references('id')->on('medical_tests')->onDelete('restrict');
            }

            // إضافة فهارس
            $table->index('item_type');
            $table->index('medical_test_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_request_lines', function (Blueprint $table) {
            $table->dropForeign(['medical_test_id']);
            $table->dropColumn(['medical_test_id', 'item_type']);
            $table->foreignId('medicine_id')->nullable(false)->change();
        });
    }
};

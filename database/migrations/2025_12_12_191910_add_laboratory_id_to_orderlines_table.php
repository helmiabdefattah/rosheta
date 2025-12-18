<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::table('order_lines', function (Blueprint $table) {
    $table->unsignedBigInteger('medical_test_id')->nullable();

    // Add foreign key if medical_tests table exists
    $table->foreign('medical_test_id')
        ->references('id')
        ->on('medical_tests')
        ->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('order_lines', function (Blueprint $table) {
            $table->dropForeign(['medical_test_id']);
            $table->dropColumn('medical_test_id');
        });
    }
};

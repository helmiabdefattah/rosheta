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
        Schema::create('laboratory_test_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade');
            $table->foreignId('medical_test_id')->constrained('medical_tests')->onDelete('cascade');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
            
            // Unique constraint: one price per laboratory per test
            $table->unique(['laboratory_id', 'medical_test_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_test_prices');
    }
};

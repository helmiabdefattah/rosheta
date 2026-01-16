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
        Schema::dropIfExists('client_request_test_lines');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('client_request_test_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_request_id')->constrained('client_requests')->cascadeOnDelete();
            $table->foreignId('medical_test_id')->constrained('medical_tests')->restrictOnDelete();
            $table->timestamps();
        });
    }
};

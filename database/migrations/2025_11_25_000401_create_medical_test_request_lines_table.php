<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_test_request_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_test_request_id')->constrained('medical_test_requests')->cascadeOnDelete();
            $table->foreignId('medical_test_id')->constrained('medical_tests')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_test_request_lines');
    }
};





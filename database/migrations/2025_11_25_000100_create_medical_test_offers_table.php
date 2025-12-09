<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_test_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_test_id')->constrained('medical_tests')->cascadeOnDelete();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->decimal('discount', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['medical_test_id', 'laboratory_id'], 'uniq_test_lab_offer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_test_offers');
    }
};





<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers');
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->unsignedInteger('quantity');
            $table->string('unit');
            $table->decimal('price', 10, 2)->nullable(); // agent must fill
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_lines');
    }
};

















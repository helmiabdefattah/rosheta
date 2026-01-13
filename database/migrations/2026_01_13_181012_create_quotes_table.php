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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // Laboratory, Pharmacy, etc.
            $table->unsignedBigInteger('model_id');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->text('quote');
            $table->text('reply')->nullable();
            $table->timestamps();

            // Index for polymorphic relationship
            $table->index(['model_type', 'model_id']);
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};

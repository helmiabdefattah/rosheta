<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('client_address_id')->constrained('client_addresses')->restrictOnDelete();

            $table->boolean('pregnant')->default(false);
            $table->boolean('diabetic')->default(false);
            $table->boolean('heart_patient')->default(false);
            $table->boolean('high_blood_pressure')->default(false);
            $table->text('note')->nullable();

            $table->string('status')->default('pending'); // pending, approved, rejected

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};




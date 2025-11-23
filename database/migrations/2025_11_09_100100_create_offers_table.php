<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_request_id')->constrained('client_requests');
            $table->foreignId('pharmacy_id')->constrained('pharmacies');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status')->default('draft'); // draft, submitted, accepted, cancelled
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};









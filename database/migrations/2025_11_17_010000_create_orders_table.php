<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_request_id')->constrained('client_requests');
            $table->foreignId('pharmacy_id')->constrained('pharmacies');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->string('status')->default('preparing'); // preparing, delivering, delivered
            $table->string('payment_method')->nullable(); // cash, visa, etc.
            $table->boolean('payed')->default(false);
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};












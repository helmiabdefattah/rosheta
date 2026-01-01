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
		Schema::create('nurse_offers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('home_nurse_request_id')->constrained('home_nurse_requests')->cascadeOnDelete();
			$table->foreignId('nurse_id')->constrained('nurses')->cascadeOnDelete();
			$table->decimal('price', 10, 2);
			$table->text('notes')->nullable();
			$table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
			$table->timestamps();

			$table->unique(['home_nurse_request_id', 'nurse_id']);
			$table->index(['status']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('nurse_offers');
	}
};



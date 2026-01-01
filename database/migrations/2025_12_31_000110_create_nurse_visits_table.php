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
		Schema::create('nurse_visits', function (Blueprint $table) {
			$table->id();
			$table->foreignId('home_nurse_request_id')->constrained('home_nurse_requests')->cascadeOnDelete();
			$table->foreignId('nurse_id')->nullable()->constrained('nurses')->nullOnDelete();
			$table->dateTime('visit_datetime');
			$table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])->default('scheduled');
			$table->text('notes')->nullable();
			$table->timestamps();

			$table->index(['home_nurse_request_id']);
			$table->index(['nurse_id']);
			$table->index(['visit_datetime']);
			$table->index(['status']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('nurse_visits');
	}
};



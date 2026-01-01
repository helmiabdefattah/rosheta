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
		Schema::create('home_nurse_requests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
			$table->foreignId('address_id')->nullable()->constrained('client_addresses')->nullOnDelete();
			$table->foreignId('nurse_id')->nullable()->constrained('nurses')->nullOnDelete();
			$table->string('service_type');
			$table->text('medical_notes')->nullable();
			$table->unsignedInteger('visits_count')->default(1);
			$table->enum('visit_frequency', ['daily', 'every_two_days', 'once_weekly', 'twice_weekly'])->default('daily');
			$table->date('visit_start_date');
			$table->time('visit_time');
			$table->boolean('needs_overnight')->default(false);
			$table->unsignedTinyInteger('overnight_days')->nullable();
			$table->decimal('total_price', 10, 2)->nullable();
			$table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
			$table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
			$table->timestamps();

			$table->index(['client_id']);
			$table->index(['nurse_id']);
			$table->index(['status']);
			$table->index(['visit_start_date']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('home_nurse_requests');
	}
};



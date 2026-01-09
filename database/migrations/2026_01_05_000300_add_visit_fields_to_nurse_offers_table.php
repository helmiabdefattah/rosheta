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
		Schema::table('nurse_offers', function (Blueprint $table) {
			$table->enum('visit_period', ['daily', 'every_two_days', 'once_weekly', 'twice_weekly'])->nullable()->after('notes');
			$table->unsignedInteger('visits_count')->nullable()->after('visit_period');
			$table->decimal('visit_price', 10, 2)->nullable()->after('visits_count');
			$table->decimal('total_price', 10, 2)->nullable()->after('visit_price');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('nurse_offers', function (Blueprint $table) {
			$table->dropColumn(['visit_period', 'visits_count', 'visit_price', 'total_price']);
		});
	}
};





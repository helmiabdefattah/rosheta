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
			$table->time('visit_start_time')->nullable()->after('visit_period');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('nurse_offers', function (Blueprint $table) {
			$table->dropColumn('visit_start_time');
		});
	}
};




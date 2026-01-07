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
			$table->unsignedSmallInteger('visit_duration')->nullable()->after('visit_start_time');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('nurse_offers', function (Blueprint $table) {
			$table->dropColumn('visit_duration');
		});
	}
};




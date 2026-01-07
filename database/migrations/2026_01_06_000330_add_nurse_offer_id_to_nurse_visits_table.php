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
		Schema::table('nurse_visits', function (Blueprint $table) {
			$table->foreignId('nurse_offer_id')->nullable()->after('nurse_id')->constrained('nurse_offers')->nullOnDelete();
			$table->index('nurse_offer_id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('nurse_visits', function (Blueprint $table) {
			$table->dropConstrainedForeignId('nurse_offer_id');
		});
	}
};




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
		Schema::table('home_nurse_requests', function (Blueprint $table) {
			$table->enum('preferred_gender', ['male', 'female'])->nullable()->after('service_type');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('home_nurse_requests', function (Blueprint $table) {
			$table->dropColumn('preferred_gender');
		});
	}
};

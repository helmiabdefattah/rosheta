<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('bonus_points', function (Blueprint $table) {
			$table->boolean('used')->default(false)->after('points');
			$table->string('status', 20)->default('active')->after('used');
			$table->index(['used', 'status']);
		});
	}

	public function down(): void
	{
		Schema::table('bonus_points', function (Blueprint $table) {
			$table->dropIndex(['used', 'status']);
			$table->dropColumn(['used', 'status']);
		});
	}
};




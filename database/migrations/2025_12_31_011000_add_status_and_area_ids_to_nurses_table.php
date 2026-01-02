<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('nurses', function (Blueprint $table) {
			if (!Schema::hasColumn('nurses', 'status')) {
				$table->enum('status', ['active', 'inactive'])->default('active')->after('address');
			}
			if (!Schema::hasColumn('nurses', 'area_ids')) {
				$table->json('area_ids')->nullable()->after('status');
			}
		});
	}

	public function down(): void
	{
		Schema::table('nurses', function (Blueprint $table) {
			if (Schema::hasColumn('nurses', 'area_ids')) {
				$table->dropColumn('area_ids');
			}
			if (Schema::hasColumn('nurses', 'status')) {
				$table->dropColumn('status');
			}
		});
	}
};



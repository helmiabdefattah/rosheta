<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (Schema::hasTable('medical_tests')) {
			Schema::table('medical_tests', function (Blueprint $table) {
				if (!Schema::hasColumn('medical_tests', 'type')) {
					$table->enum('type', ['test', 'radiology'])->default('test')->after('conditions');
					$table->index('type');
				}
			});

			// Ensure existing records are marked as test (safety in case default didn't apply)
			DB::table('medical_tests')->whereNull('type')->update(['type' => 'test']);
		}

		// Import radiology tests into medical_tests if a radiology table exists
		$sourceTable = null;
		if (Schema::hasTable('radiology_tests')) {
			$sourceTable = 'radiology_tests';
		} elseif (Schema::hasTable('diagnostic_radiology')) {
			$sourceTable = 'diagnostic_radiology';
		}

		if ($sourceTable && Schema::hasTable('medical_tests')) {
			// Map columns from source to destination
			$hasNameEn = Schema::hasColumn($sourceTable, 'name_en');
			$hasNameAr = Schema::hasColumn($sourceTable, 'name_ar');
			$hasDescription = Schema::hasColumn($sourceTable, 'description');
			$hasConditions = Schema::hasColumn($sourceTable, 'conditions');

			$chunkSize = 500;
			DB::table($sourceTable)->orderBy('id')->chunk($chunkSize, function ($rows) use ($hasNameEn, $hasNameAr, $hasDescription, $hasConditions) {
				$now = now();
				$inserts = [];
				foreach ($rows as $row) {
					$inserts[] = [
						'test_name_en' => $hasNameEn ? $row->name_en : null,
						'test_name_ar' => $hasNameAr ? $row->name_ar : null,
						'test_description' => $hasDescription ? $row->description : null,
						'conditions' => $hasConditions ? $row->conditions : null,
						'type' => 'radiology',
						'created_at' => $now->toDateString(),
						'updated_at' => $now->toDateString(),
					];
				}

				if (!empty($inserts)) {
					DB::table('medical_tests')->insert($inserts);
				}
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Attempt to delete imported radiology rows (heuristic: rows with type='radiology')
		if (Schema::hasTable('medical_tests') && Schema::hasColumn('medical_tests', 'type')) {
			DB::table('medical_tests')->where('type', 'radiology')->delete();

			Schema::table('medical_tests', function (Blueprint $table) {
				$table->dropIndex(['type']);
				$table->dropColumn('type');
			});
		}
	}
};



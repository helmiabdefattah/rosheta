<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The alternative medicine gets the same detail as the primary one.
 *
 * prescription_items already carried substitute_name on its own, which left the
 * pharmacy with an alternative and no dose to dispense it by. medical_plan_items
 * had no substitute column at all, so saving a prescription as a plan silently
 * dropped the alternative — the plan came back missing it.
 */
return new class extends Migration
{
    private const DETAILS = ['substitute_dose', 'substitute_frequency', 'substitute_duration', 'substitute_instructions'];

    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $after = 'substitute_name';
            foreach (self::DETAILS as $column) {
                if (! Schema::hasColumn('prescription_items', $column)) {
                    $table->string($column)->nullable()->after($after);
                    $after = $column;
                }
            }
        });

        Schema::table('medical_plan_items', function (Blueprint $table) {
            if (! Schema::hasColumn('medical_plan_items', 'substitute_name')) {
                $table->string('substitute_name')->nullable()->after('medicine_name');
            }

            $after = 'substitute_name';
            foreach (self::DETAILS as $column) {
                if (! Schema::hasColumn('medical_plan_items', $column)) {
                    $table->string($column)->nullable()->after($after);
                    $after = $column;
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(self::DETAILS);
        });

        Schema::table('medical_plan_items', function (Blueprint $table) {
            $table->dropColumn(array_merge(['substitute_name'], self::DETAILS));
        });
    }
};

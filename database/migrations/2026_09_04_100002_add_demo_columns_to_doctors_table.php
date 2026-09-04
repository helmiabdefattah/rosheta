<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The demo tenant marker.
 *
 * It goes on `doctors`, not `clinics`, because the doctor is the real tenant
 * here: a doctor owns several clinics, and billable_items, medical_plans,
 * examination_fields, insurance_collections and conversations are keyed by
 * doctor_id with no clinic_id at all. Marking only the clinic would leave
 * those five tables unmarked and un-purgeable.
 *
 * Applied to production as well as the demo database so the two structures
 * stay identical (demo:setup copies the structure from production). In
 * production the column simply stays false for every row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->index()->after('id');
            }
            if (! Schema::hasColumn('doctors', 'demo_session_id')) {
                $table->uuid('demo_session_id')->nullable()->index()->after('is_demo');
            }
            if (! Schema::hasColumn('doctors', 'demo_expires_at')) {
                $table->timestamp('demo_expires_at')->nullable()->index()->after('demo_session_id');
            }
            if (! Schema::hasColumn('doctors', 'demo_last_activity_at')) {
                $table->timestamp('demo_last_activity_at')->nullable()->after('demo_expires_at');
            }
            if (! Schema::hasColumn('doctors', 'demo_template_key')) {
                $table->string('demo_template_key')->nullable()->after('demo_last_activity_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'is_demo', 'demo_session_id', 'demo_expires_at',
                'demo_last_activity_at', 'demo_template_key',
            ]);
        });
    }
};

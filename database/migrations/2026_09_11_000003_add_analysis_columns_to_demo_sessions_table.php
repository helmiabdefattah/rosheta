<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The verdict on a finished run, written once — at the moment the tenant is
 * destroyed — so the admin list can read it without the tenant existing.
 *
 * activity_summary holds the whole analysis: what the visitor created (counted
 * against a baseline taken right after seeding, so the seeded clinic is not
 * mistaken for their work), which features they touched, and where they
 * stopped. The counters beside it are the same numbers lifted out so the list
 * can sort and filter on them in SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demo_sessions', function (Blueprint $table) {
            $table->json('activity_summary')->nullable()->after('steps_completed');

            // Row counts per tenant table taken immediately after seeding. The
            // subtrahend of everything in activity_summary['created'].
            $table->json('baseline_counts')->nullable()->after('activity_summary');

            $table->unsignedInteger('actions_count')->default(0)->after('baseline_counts');
            $table->unsignedInteger('pages_count')->default(0)->after('actions_count');

            // Time between their first and last recorded event — the part of
            // the window they were actually present for, which is not the same
            // as started_at -> ended_at when they walk away mid-run.
            $table->unsignedInteger('active_seconds')->nullable()->after('pages_count');

            $table->timestamp('analyzed_at')->nullable()->after('purged_at');
        });
    }

    public function down(): void
    {
        Schema::table('demo_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'activity_summary', 'baseline_counts', 'actions_count',
                'pages_count', 'active_seconds', 'analyzed_at',
            ]);
        });
    }
};

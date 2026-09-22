<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `source` / `external_ref` to subscription_requests for the Facebook Lead
 * Ads webhook. Kept as a separate, guarded migration so databases that already
 * ran the create migration (before those columns were added to it) get the new
 * columns without a rollback. Guards make it a no-op where they already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_requests', 'source')) {
                $table->string('source')->default('web')->after('notes');
                $table->index('source');
            }
            if (! Schema::hasColumn('subscription_requests', 'external_ref')) {
                $table->string('external_ref')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_requests', 'external_ref')) {
                $table->dropColumn('external_ref');
            }
            if (Schema::hasColumn('subscription_requests', 'source')) {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            }
        });
    }
};

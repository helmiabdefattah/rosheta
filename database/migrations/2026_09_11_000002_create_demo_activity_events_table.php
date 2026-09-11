<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every meaningful thing a demo visitor did, in order.
 *
 * On the PRODUCTION connection for the same reason as demo_sessions and
 * demo_surveys: the whole point is that it outlives the tenant, which is
 * hard-deleted moments after the run ends. Written by demo requests, so the
 * table is on the prod_write_allowlist and out of the demo database entirely
 * (skip_tables) — see config/demo.php.
 *
 * demo_session_id carries no foreign key on purpose: the session row lives on
 * this connection, but the events have to stand on their own if it is ever
 * trimmed.
 *
 * PRIVACY: labels and route names only. No request payloads, no free text the
 * visitor typed, no file contents — what they clicked, never what they wrote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_activity_events', function (Blueprint $table) {
            $table->id();

            $table->uuid('demo_session_id');

            // Clock of the event itself, not of the row: a batched write must
            // not reorder the journey.
            $table->timestamp('occurred_at');

            // Which hat they were wearing at that moment.
            $table->string('role', 20)->nullable();

            // 'action' (they changed something), 'page' (they looked at a
            // screen) or 'system' (start, role switch, reset, end).
            $table->string('kind', 12)->default('page');

            // Stable slug, e.g. "prescription.create" — what the admin list
            // groups and counts on.
            $table->string('action', 80);

            // Human sentence for the timeline, stored rather than derived so
            // an old run still reads correctly after the catalogue changes.
            $table->string('label')->nullable();
            $table->string('label_en')->nullable();

            $table->string('method', 10)->nullable();
            $table->string('route')->nullable();
            $table->string('path')->nullable();
            $table->unsignedSmallInteger('status')->nullable();

            // Route parameters and counters only (ids, how many items).
            $table->json('meta')->nullable();

            // The journey is always read per session, in order.
            $table->index(['demo_session_id', 'occurred_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_activity_events');
    }
};

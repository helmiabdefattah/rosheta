<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the visitor said on their way out of the demo.
 *
 * On the PRODUCTION connection, for the same reason as demo_sessions: the
 * whole point of the answers is that they survive the tenant being wiped a
 * second later. They are written by a demo request, so the table is on the
 * prod_write_allowlist (config/demo.php) and out of the demo database
 * entirely (skip_tables).
 *
 * demo_session_id is a plain uuid column with no foreign key — the row it
 * points at is also on this connection, but the survey has to stand on its
 * own when a visitor answers after their session record is long gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_surveys', function (Blueprint $table) {
            $table->id();

            // One survey per run. Nullable: an answer that arrives without a
            // valid signed token is still worth keeping, just unattributed.
            $table->uuid('demo_session_id')->nullable()->unique();

            // Q1 — was the system useful? The only required answer.
            $table->boolean('was_useful');

            // Q2 — anything they want added.
            $table->text('wants_added')->nullable();

            // Q3 — anything they disliked or want removed.
            $table->text('wants_removed')->nullable();

            // Snapshots taken at answer time so the admin list reads on its
            // own after the session row has been purged or trimmed.
            $table->string('role', 20)->nullable();
            $table->string('specialty')->nullable();

            $table->string('ip_hash', 64)->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_surveys');
    }
};

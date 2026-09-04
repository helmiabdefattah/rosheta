<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demo funnel records. These live on the PRODUCTION connection on purpose:
 * they are marketing data that must outlive the demo tenant, and they are the
 * only production writes a demo request is allowed to make
 * (see config/demo.php -> prod_write_allowlist).
 *
 * doctor_id / doctor_user_id / assistant_user_id point at rows in the DEMO
 * database, so they carry no foreign key — MySQL cannot enforce one across
 * databases. They are nulled when the tenant is purged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Tenant in the demo database (no FK — different database).
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedBigInteger('doctor_user_id')->nullable();
            $table->unsignedBigInteger('assistant_user_id')->nullable();

            $table->enum('started_role', ['doctor', 'assistant'])->default('doctor');
            $table->string('template_key')->nullable();
            $table->string('specialty')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();
            $table->enum('end_reason', ['expired', 'idle', 'user_ended', 'converted', 'purged'])
                ->nullable();
            $table->timestamp('purged_at')->nullable();

            $table->json('steps_completed')->nullable();
            $table->unsignedBigInteger('converted_doctor_id')->nullable();

            // Ad attribution.
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('gclid')->nullable();
            $table->string('ttclid')->nullable();

            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('country', 2)->nullable();
            $table->string('device', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_sessions');
    }
};

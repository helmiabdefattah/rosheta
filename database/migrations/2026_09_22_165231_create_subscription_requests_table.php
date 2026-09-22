<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription requests submitted from the public pricing page.
 *
 * A prospective clinic fills in the doctor / clinic / assistant details and a
 * contact number; the team then reviews it and creates the real accounts by
 * hand (via Clinic Quick Setup) within 24h. Chosen passwords are stored
 * encrypted at rest — never in plain text — and only surfaced to an admin on
 * the request's detail page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();

            // Chosen plan / add-on.
            $table->string('plan')->nullable();               // starter | equipped | multi
            $table->string('billing')->nullable();            // monthly | annual
            $table->boolean('with_profile_site')->default(false);
            $table->string('profile_subdomain')->nullable();  // {name}.mostashfaon.com

            // Whom to call back.
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();

            // Doctor account to create.
            $table->string('doctor_name')->nullable();
            $table->string('doctor_specialty')->nullable();
            $table->string('doctor_username')->nullable();
            $table->string('doctor_phone')->nullable();
            $table->string('doctor_email')->nullable();
            $table->text('doctor_password')->nullable();       // encrypted

            // Clinic.
            $table->string('clinic_name')->nullable();
            $table->string('clinic_address')->nullable();
            $table->string('clinic_city')->nullable();
            $table->string('clinic_phone')->nullable();

            // Assistant accounts: [{name, username, phone, password}, ...].
            $table->text('assistants')->nullable();            // encrypted JSON

            $table->text('notes')->nullable();

            // Handling.
            $table->string('status')->default('new');          // new | contacted | activated
            $table->timestamp('reviewed_at')->nullable();

            $table->string('locale', 8)->nullable();
            $table->string('ip', 64)->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};

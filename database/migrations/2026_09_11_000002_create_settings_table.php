<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A small key/value store for settings an administrator changes at runtime.
 *
 * Everything here used to live in .env, which means a deploy to change a URL.
 * The first pair of keys is the "Try it free" invitation on the public pages
 * (demo_invite.enabled / demo_invite.url); the table is deliberately generic
 * so the next such switch does not need another migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();

            // Nullable and text: a setting can be a flag, a URL or a
            // paragraph, and "not set" has to be distinguishable from "".
            $table->text('value')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

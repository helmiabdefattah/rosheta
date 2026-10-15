<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user preference for the clinic workspace navigation: 'buttons' (actions as
 * a toolbar, the default) or 'menu' (an admin-style side menu). Each user picks
 * their own, from clinic settings or the quick ☰ toggle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nav_style', 20)->default('buttons')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nav_style');
        });
    }
};

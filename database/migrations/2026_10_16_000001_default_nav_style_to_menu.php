<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The clinic workspace now opens in the admin-style side menu by default. Flip
 * the column default from 'buttons' to 'menu', and bring existing rows that
 * still hold the old default across — the preference is only three days old, so
 * a stored 'buttons' is the former default, not a deliberate choice. Users can
 * still switch back to the toolbar from clinic settings or the ☰ toggle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nav_style', 20)->default('menu')->change();
        });

        DB::table('users')->where('nav_style', 'buttons')->update(['nav_style' => 'menu']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nav_style', 20)->default('buttons')->change();
        });
    }
};

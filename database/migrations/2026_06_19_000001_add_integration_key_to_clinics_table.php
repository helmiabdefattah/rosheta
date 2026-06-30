<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Secret key used by an external clinic-management system to
            // authenticate against the /api/integration/* endpoints. Each
            // clinic that integrates gets its own key.
            $table->string('integration_key')->nullable()->unique()->after('slots_per_interval');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('integration_key');
        });
    }
};

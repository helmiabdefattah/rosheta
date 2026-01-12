<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('home_nurse_requests', function (Blueprint $table) {
            // Add custom_visit_days column
            $table->json('custom_visit_days')->nullable()->after('visit_frequency');
            
            // Modify enum to include 'custom' and 'weekly'
            DB::statement("ALTER TABLE home_nurse_requests MODIFY COLUMN visit_frequency ENUM('daily', 'every_two_days', 'weekly', 'custom') DEFAULT 'daily'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_nurse_requests', function (Blueprint $table) {
            $table->dropColumn('custom_visit_days');
            DB::statement("ALTER TABLE home_nurse_requests MODIFY COLUMN visit_frequency ENUM('daily', 'every_two_days', 'once_weekly', 'twice_weekly') DEFAULT 'daily'");
        });
    }
};

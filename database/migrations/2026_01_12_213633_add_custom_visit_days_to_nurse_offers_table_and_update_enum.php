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
        Schema::table('nurse_offers', function (Blueprint $table) {
            // Add custom_visit_days column
            $table->json('custom_visit_days')->nullable()->after('visit_period');
            
            // Modify enum to include 'weekly' and 'custom'
            DB::statement("ALTER TABLE nurse_offers MODIFY COLUMN visit_period ENUM('daily', 'every_two_days', 'weekly', 'custom') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nurse_offers', function (Blueprint $table) {
            $table->dropColumn('custom_visit_days');
            DB::statement("ALTER TABLE nurse_offers MODIFY COLUMN visit_period ENUM('daily', 'every_two_days', 'once_weekly', 'twice_weekly') NULL");
        });
    }
};

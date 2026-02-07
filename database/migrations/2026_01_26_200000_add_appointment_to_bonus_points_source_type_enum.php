<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bonus_points MODIFY COLUMN source_type ENUM('order', 'nurse_visit', 'welcome', 'appointment') NOT NULL");
    }

    public function down(): void
    {
        DB::table('bonus_points')->where('source_type', 'appointment')->delete();
        DB::statement("ALTER TABLE bonus_points MODIFY COLUMN source_type ENUM('order', 'nurse_visit', 'welcome') NOT NULL");
    }
};

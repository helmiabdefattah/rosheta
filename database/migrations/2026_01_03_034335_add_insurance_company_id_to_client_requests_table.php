<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_requests', function (Blueprint $table) {
            $table->foreignId('insurance_company_id')->nullable()->after('client_address_id')->constrained('insurance_companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_requests', function (Blueprint $table) {
            $table->dropForeign(['insurance_company_id']);
            $table->dropColumn('insurance_company_id');
        });
    }
};

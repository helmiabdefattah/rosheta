<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_test_offers', function (Blueprint $table) {
            $table->foreignId('client_request_id')->nullable()->after('laboratory_id')->constrained('client_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_test_offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_request_id');
        });
    }
};



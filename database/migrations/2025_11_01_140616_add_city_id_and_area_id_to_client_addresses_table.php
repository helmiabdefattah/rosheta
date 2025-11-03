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
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete()->after('location');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete()->after('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropConstrainedForeignId('area_id');
        });
    }
};

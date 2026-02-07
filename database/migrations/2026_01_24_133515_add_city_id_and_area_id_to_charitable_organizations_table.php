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
        Schema::table('charitable_organizations', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('address')->constrained()->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            
            $table->index('city_id');
            $table->index('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charitable_organizations', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['area_id']);
            $table->dropIndex(['city_id']);
            $table->dropIndex(['area_id']);
            $table->dropColumn(['city_id', 'area_id']);
        });
    }
};

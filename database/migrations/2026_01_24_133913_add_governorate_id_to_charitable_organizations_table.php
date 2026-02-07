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
            $table->foreignId('governorate_id')->nullable()->after('address')->constrained()->nullOnDelete();
            $table->index('governorate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charitable_organizations', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropIndex(['governorate_id']);
            $table->dropColumn('governorate_id');
        });
    }
};

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
            $table->string('model_type')->nullable()->after('type');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_requests', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropColumn(['model_type', 'model_id']);
        });
    }
};

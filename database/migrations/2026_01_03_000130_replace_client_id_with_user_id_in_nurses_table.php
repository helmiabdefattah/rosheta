<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add user_id and reference users
        Schema::table('nurses', function (Blueprint $table) {
            if (!Schema::hasColumn('nurses', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            }
        });

        // Drop client_id if it exists
        Schema::table('nurses', function (Blueprint $table) {
            if (Schema::hasColumn('nurses', 'client_id')) {
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign doesn't exist
                }
                $table->dropColumn('client_id');
            }
        });
    }

    public function down(): void
    {
        // Re-add client_id and reference clients
        Schema::table('nurses', function (Blueprint $table) {
            if (!Schema::hasColumn('nurses', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->cascadeOnDelete();
            }
        });

        // Drop user_id
        Schema::table('nurses', function (Blueprint $table) {
            if (Schema::hasColumn('nurses', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign doesn't exist
                }
                $table->dropColumn('user_id');
            }
        });
    }
};



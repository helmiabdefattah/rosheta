<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charitable_organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('charitable_organizations', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('charitable_organizations', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('services');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'charitable_organization_id')) {
                $table->foreignId('charitable_organization_id')
                    ->nullable()
                    ->after('nurse_id')
                    ->constrained('charitable_organizations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'charitable_organization_id')) {
                $table->dropConstrainedForeignId('charitable_organization_id');
            }
        });

        Schema::table('charitable_organizations', function (Blueprint $table) {
            if (Schema::hasColumn('charitable_organizations', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('charitable_organizations', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};

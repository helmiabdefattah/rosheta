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
        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'request_type')) {
                $table->string('request_type')->default('medicine')->after('client_request_id');
            }

            if (!Schema::hasColumn('offers', 'laboratory_id')) {
                $table->foreignId('laboratory_id')->nullable()->after('pharmacy_id');
            }

            if (Schema::hasColumn('offers', 'pharmacy_id')) {
                $table->foreignId('pharmacy_id')->nullable()->change();
            }

            $table->index('request_type');
            $table->index('laboratory_id');

            if (Schema::hasTable('laboratories')) {
                $table->foreign('laboratory_id')->references('id')->on('laboratories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // حذف المفاتيح الخارجية
            $table->dropForeign(['laboratory_id']);

            // حذف الأعمدة
            $table->dropColumn(['request_type', 'laboratory_id']);

            // إعادة pharmacy_id إلى not null
            $table->foreignId('pharmacy_id')->nullable(false)->change();

            // حذف الفهارس
            $table->dropIndex(['request_type']);
            $table->dropIndex(['laboratory_id']);
        });
    }
};

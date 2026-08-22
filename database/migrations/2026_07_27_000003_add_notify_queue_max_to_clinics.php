<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The cap used when notifying waiting patients of how many reservations are
 * ahead of them: once more than this many are waiting, the push says
 * "more than N patients before you" instead of the exact number. Default 10.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'notify_queue_max')) {
                $table->unsignedSmallInteger('notify_queue_max')->default(10)->after('print_qr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('notify_queue_max');
        });
    }
};

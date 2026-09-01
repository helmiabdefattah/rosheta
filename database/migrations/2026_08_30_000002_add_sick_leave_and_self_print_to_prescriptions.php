<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two additions to a prescription:
 *
 * - sick_leave_days: the rest the doctor recommends, printed on every copy so
 *   the patient has it in writing for their employer.
 * - self_printed_at: stamped the first time a patient prints their own copy
 *   from the waiting-room screen. The stamp is the lock — that route is public,
 *   so it must hand out one copy and no more.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('prescriptions', 'sick_leave_days')) {
                $table->unsignedSmallInteger('sick_leave_days')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('prescriptions', 'self_printed_at')) {
                $table->timestamp('self_printed_at')->nullable()->after('sick_leave_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['sick_leave_days', 'self_printed_at']);
        });
    }
};

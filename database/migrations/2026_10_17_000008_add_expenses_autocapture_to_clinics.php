<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in: when on, a dental clinic's payments to its labs are mirrored into the
 * expenses ledger automatically (under "Dental Lab Fees"). Off by default so
 * nothing about existing dental flows changes until a clinic turns it on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('expenses_autocapture')->default(false)->after('notify_queue_max');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('expenses_autocapture');
        });
    }
};

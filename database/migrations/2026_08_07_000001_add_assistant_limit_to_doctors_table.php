<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many assistant accounts a doctor may create *per clinic*. Every doctor
 * starts at 2; the admin raises it for doctors who need a bigger front desk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'assistant_limit')) {
                $table->unsignedSmallInteger('assistant_limit')->default(2)->after('brief');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'assistant_limit')) {
                $table->dropColumn('assistant_limit');
            }
        });
    }
};

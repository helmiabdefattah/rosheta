<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link an expense to the vendor it was billed from (optional). Deleting a
 * vendor keeps its expenses, just unlinked (nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('expense_category_id')
                ->constrained('vendors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};

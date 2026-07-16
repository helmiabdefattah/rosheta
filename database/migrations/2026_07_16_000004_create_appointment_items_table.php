<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A chargeable line on a visit: what was done, how many, at what price.
 *
 * name and unit_price are snapshots taken when the line is added, so editing
 * the catalog price later never silently rewrites what a past patient owed.
 * billable_item_id only records which catalog entry it came from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billable_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();

            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clinic stock of dental materials/consumables. `quantity_on_hand` is kept in
 * step with the stock movement ledger; `reorder_level` drives the low-stock
 * warning. Optionally linked to a clinic_general catalog item so a lab order can
 * deduct the right material automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dental_item_id')->nullable()->constrained('dental_items')->nullOnDelete();
            $table->string('name');
            $table->string('unit'); // piece | box | gram | ml | …
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_stock_items');
    }
};

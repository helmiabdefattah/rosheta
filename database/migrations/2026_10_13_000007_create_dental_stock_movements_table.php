<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger of every change to a stock item's quantity. `quantity` is signed
 * (positive = added, negative = consumed); `type` records the intent. The
 * optional polymorphic `source` links a movement to what caused it — e.g. a
 * lab order consuming materials when it is marked received — so that cause can
 * be reversed cleanly if the order changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_stock_item_id')->constrained('dental_stock_items')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // in | out | adjust
            $table->decimal('quantity', 12, 2); // signed: +in / -out
            $table->string('reason')->nullable();
            $table->nullableMorphs('source'); // e.g. a DentalLabOrder
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['dental_stock_item_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_stock_movements');
    }
};

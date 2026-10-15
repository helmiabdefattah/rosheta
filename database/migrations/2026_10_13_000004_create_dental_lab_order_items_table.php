<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One line on a lab order. `level` / `name` / `unit` are snapshots from the
 * catalog at order time, so later catalog edits never rewrite past orders.
 * Price is entered per line (labs charge differently), line total = qty × price.
 *
 * `teeth` is used only when the order is in per-item teeth mode; otherwise the
 * order-level teeth apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_lab_order_id')->constrained('dental_lab_orders')->cascadeOnDelete();
            // Catalog reference is nullable so a one-off line can be typed freely.
            $table->foreignId('dental_item_id')->nullable()->constrained('dental_items')->nullOnDelete();
            $table->string('level');  // snapshot: patient | tooth | clinic_general
            $table->string('name');   // snapshot
            $table->string('unit');   // snapshot: piece | arch | tooth | unit | gram | ml | box
            // Decimal so materials (grams / ml) work as well as whole counts.
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->json('teeth')->nullable(); // per-item Palmer codes (per-item mode)
            $table->timestamps();

            $table->index('dental_lab_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_lab_order_items');
    }
};

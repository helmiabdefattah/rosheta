<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * External dental laboratories a clinic sends work to. Scoped to the clinic, so
 * each clinic keeps its own list of labs it deals with.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            // Staff user who added it; kept even if that account is removed.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_labs');
    }
};

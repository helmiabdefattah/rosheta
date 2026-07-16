<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money taken from a patient at the front desk for an appointment.
 *
 * A row per payment rather than a single column, so a visit can be settled in
 * parts and each entry keeps an audit trail of who took it and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            // The staff user who took the payment; kept even if they're removed.
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('collected_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};

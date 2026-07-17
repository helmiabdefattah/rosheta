<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lump-sum money received from an insurance company (a payout against claims).
 * Recorded manually by the doctor. A company's "total collected" is the sum of
 * these; "pending" = total claimed (appointment_insurances) − total collected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_company_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->date('collected_on');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['doctor_id', 'insurance_company_id', 'collected_on'], 'ins_coll_doctor_company_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_collections');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suppliers an expense can be billed from (labs, landlords, utility companies,
 * …). `dental_lab_id` optionally ties a vendor to a dental lab so the vendor
 * statement and the lab's own statement reconcile. `opening_balance` carries a
 * starting balance brought forward before the module was used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dental_lab_id')->nullable()->constrained('dental_labs')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('supplier'); // VendorType
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('medicines')) {
            return; // Table already exists
        }

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('api_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('arabic')->nullable();
            $table->string('shortage')->nullable();
            $table->string('date_updated')->nullable();
            $table->string('imported')->nullable();
            $table->string('percentage')->nullable();
            $table->text('pharmacology')->nullable();
            $table->string('route')->nullable();
            $table->boolean('in_eye')->default(false);
            $table->string('units')->nullable();
            $table->string('small_unit')->nullable();
            $table->integer('sold_times')->default(0);
            $table->string('dosage_form')->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcode2')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('anyupdatedate')->nullable();
            $table->string('new_added_date')->nullable();
            $table->string('updtime')->nullable();
            $table->boolean('fame')->default(false);
            $table->boolean('cosmo')->default(false);
            $table->string('dose')->nullable();
            $table->boolean('repeated')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('oldprice', 10, 2)->nullable();
            $table->decimal('newprice', 10, 2)->nullable();
            $table->string('active_ingredient')->nullable();
            $table->string('similars_id')->nullable();
            $table->string('img')->nullable();
            $table->text('description')->nullable();
            $table->text('uses')->nullable();
            $table->string('company')->nullable();
            $table->boolean('reported')->default(false);
            $table->string('reporter_name')->nullable();
            $table->integer('visits')->default(0);
            $table->integer('sim_visits')->default(0);
            $table->integer('ind_visits')->default(0);
            $table->integer('composition_visits')->default(0);
            $table->integer('order_visits')->default(0);
            $table->integer('app_visits')->default(0);
            $table->integer('shares')->default(0);
            $table->string('last_visited')->nullable();
            $table->string('lastupdatingadmin')->nullable();
            $table->integer('awhat_visits')->default(0);
            $table->text('report_msg')->nullable();
            $table->text('found_pharmacies_ids')->nullable();
            $table->integer('availability')->default(0);
            $table->string('noimgid')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('search_term')->nullable();
            $table->integer('batch_number')->nullable();
            $table->timestamps();

            // $table->index('api_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

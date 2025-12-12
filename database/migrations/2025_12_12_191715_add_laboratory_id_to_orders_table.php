<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('laboratory_id')->nullable()->after('pharmacy_id');

            // If you have laboratories table and want FK:
            $table->foreign('laboratory_id')
                ->references('id')
                ->on('laboratories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['laboratory_id']);
            $table->dropColumn('laboratory_id');
        });
    }

};

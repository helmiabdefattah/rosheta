<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_doctor', function (Blueprint $table) {
            $table->decimal('medical_examination_price', 10, 2)->nullable()->after('doctor_id');
            $table->decimal('follow_up_price', 10, 2)->nullable()->after('medical_examination_price');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_doctor', function (Blueprint $table) {
            $table->dropColumn(['medical_examination_price', 'follow_up_price']);
        });
    }
};

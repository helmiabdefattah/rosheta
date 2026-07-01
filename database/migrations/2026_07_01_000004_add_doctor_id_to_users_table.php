<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A doctor's assistant is a `users` row linked to the doctor they work for
 * (following rosheta's existing pattern of pharmacy_id / laboratory_id /
 * nurse_id on the users table). A user with doctor_id set is an assistant;
 * a user that owns a `doctors` row (doctors.user_id) is the doctor themselves.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'doctor_id')) {
                $table->foreignId('doctor_id')->nullable()->after('nurse_id')
                    ->constrained('doctors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
};

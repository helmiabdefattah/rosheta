<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reuse the shared `appointments` table for the clinic (design) examination
 * workflow. Adds the queue / walk-in columns the clinic needs, widens the
 * status + type enums to a superset covering both systems, and introduces a
 * canonical `scheduled_at` timestamp kept in sync with appointment_date/time by
 * the Appointment model. The strict per-slot unique index is relaxed to a plain
 * index because the clinic queue puts many appointments on the same day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('appointment_time');
            }
            if (! Schema::hasColumn('appointments', 'queue_number')) {
                $table->unsignedInteger('queue_number')->nullable()->after('scheduled_at');
            }
            if (! Schema::hasColumn('appointments', 'source')) {
                $table->string('source', 20)->default('system')->after('queue_number');
            }
            if (! Schema::hasColumn('appointments', 'reason')) {
                $table->text('reason')->nullable()->after('source');
            }
        });

        // appointment_date / appointment_time may be unknown for a pure walk-in
        // (the model derives them from scheduled_at, but stay defensive).
        DB::statement('ALTER TABLE `appointments` MODIFY `appointment_date` DATE NULL');
        DB::statement('ALTER TABLE `appointments` MODIFY `appointment_time` TIME NULL');

        // Superset enums: rosheta booking values + clinic workflow values.
        DB::statement("ALTER TABLE `appointments` MODIFY `type` ENUM('medical_examination','follow_up','examination','consultation') NOT NULL DEFAULT 'medical_examination'");
        DB::statement("ALTER TABLE `appointments` MODIFY `status` ENUM('pending','confirmed','completed','missed','cancelled','scheduled','under_examination','escaped') NOT NULL DEFAULT 'pending'");

        // Relax the strict one-appointment-per-slot unique index; the clinic
        // queue books many same-day appointments. App-level slot caps remain.
        // The unique index also backs the clinic_id foreign key, so a plain
        // replacement index must exist before the unique one can be dropped.
        $uniqueName = 'appointments_clinic_id_appointment_date_appointment_time_unique';
        $newName = 'appointments_clinic_date_time_index';
        $hasUnique = collect(DB::select("SHOW INDEX FROM `appointments` WHERE Key_name = ?", [$uniqueName]))->isNotEmpty();
        $hasNew = collect(DB::select("SHOW INDEX FROM `appointments` WHERE Key_name = ?", [$newName]))->isNotEmpty();
        if ($hasUnique) {
            if (! $hasNew) {
                DB::statement("CREATE INDEX `{$newName}` ON `appointments` (`clinic_id`, `appointment_date`, `appointment_time`)");
            }
            DB::statement("ALTER TABLE `appointments` DROP INDEX `{$uniqueName}`");
        }

        // Backfill the canonical timestamp for existing rosheta bookings.
        DB::statement("UPDATE `appointments`
            SET `scheduled_at` = TIMESTAMP(`appointment_date`, COALESCE(`appointment_time`, '00:00:00'))
            WHERE `scheduled_at` IS NULL AND `appointment_date` IS NOT NULL");
    }

    public function down(): void
    {
        $indexName = 'appointments_clinic_date_time_index';
        $exists = collect(DB::select("SHOW INDEX FROM `appointments` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
        if ($exists) {
            DB::statement("ALTER TABLE `appointments` DROP INDEX `{$indexName}`");
        }

        DB::statement("ALTER TABLE `appointments` MODIFY `type` ENUM('medical_examination','follow_up') NOT NULL");
        DB::statement("ALTER TABLE `appointments` MODIFY `status` ENUM('pending','confirmed','completed','missed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'queue_number', 'source', 'reason']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notes a doctor pins to a part of the body on the examination screen: a tooth,
 * a bone, a point on an eye.
 *
 * They belong to the patient, not the visit — a dentist opening a file next
 * year sees what another dentist wrote on tooth 26 — so client_id is the anchor
 * and appointment_id only records which visit it was written during.
 *
 * One table serves every chart. Region-based charts (teeth, skeleton) use
 * `region` alone; point-based ones (eyes) also carry a relative x/y so the note
 * can be dropped anywhere, like a pin on a map.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_chart_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            // Which chart the note belongs to: teeth / skeleton / eyes.
            $table->string('chart', 32);
            // The part clicked — an FDI tooth code, a bone key, or which eye.
            $table->string('region', 64);

            // 0..1 of the region's box, for charts that pin a point rather than
            // select a whole part. Null on region-only charts.
            $table->decimal('point_x', 6, 5)->nullable();
            $table->decimal('point_y', 6, 5)->nullable();

            $table->text('note');
            $table->timestamps();

            // The screen always asks the same question: everything on this
            // patient's chart, newest first.
            $table->index(['client_id', 'chart', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_chart_notes');
    }
};

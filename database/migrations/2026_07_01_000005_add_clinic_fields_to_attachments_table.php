<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The clinic system stores patient files via rosheta's polymorphic
 * `attachments` table (attached to a Client). Preserve two clinic-specific
 * pieces of context: who uploaded the file, and an optional appointment link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('attachments', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('attachable_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('attachments', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('uploaded_by')
                    ->constrained('appointments')->nullOnDelete();
            }
            if (! Schema::hasColumn('attachments', 'title')) {
                $table->string('title')->nullable()->after('appointment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['uploaded_by', 'appointment_id', 'title']);
        });
    }
};

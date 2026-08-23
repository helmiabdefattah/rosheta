<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-doctor switch for patient chat, plus how long it stays open.
 *
 * A patient may only write to a doctor for `chat_window_days` days counted from
 * their most recent past appointment with that doctor — so the inbox stays a
 * follow-up channel for real patients instead of an open help desk. Off by
 * default: every doctor opts in from their chat page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'chat_enabled')) {
                $table->boolean('chat_enabled')->default(false)->after('assistant_limit');
            }
            if (! Schema::hasColumn('doctors', 'chat_window_days')) {
                $table->unsignedSmallInteger('chat_window_days')->default(30)->after('chat_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            foreach (['chat_enabled', 'chat_window_days'] as $column) {
                if (Schema::hasColumn('doctors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

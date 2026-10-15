<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dental chart notes can describe a tooth's condition (المشاكل) and the work
 * done on it (الأعمال) as structured values, so the chart can colour each tooth
 * and draw a symbol for its treatment. Both are optional and only used by the
 * teeth chart; other charts leave them null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinical_chart_notes', function (Blueprint $table) {
            $table->string('condition', 32)->nullable()->after('region');
            $table->string('treatment', 32)->nullable()->after('condition');
            // A note may now be just a mark, so the text itself becomes optional.
            $table->text('note')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinical_chart_notes', function (Blueprint $table) {
            $table->dropColumn(['condition', 'treatment']);
        });
    }
};

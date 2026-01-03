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
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->morphs('workable'); // workable_id, workable_type (for polymorphic relationship)
            
            // Default hours
            $table->time('default_opening_time')->nullable();
            $table->time('default_closing_time')->nullable();
            
            // Sunday
            $table->enum('sunday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('sunday_opening_time')->nullable();
            $table->time('sunday_closing_time')->nullable();
            
            // Monday
            $table->enum('monday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('monday_opening_time')->nullable();
            $table->time('monday_closing_time')->nullable();
            
            // Tuesday
            $table->enum('tuesday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('tuesday_opening_time')->nullable();
            $table->time('tuesday_closing_time')->nullable();
            
            // Wednesday
            $table->enum('wednesday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('wednesday_opening_time')->nullable();
            $table->time('wednesday_closing_time')->nullable();
            
            // Thursday
            $table->enum('thursday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('thursday_opening_time')->nullable();
            $table->time('thursday_closing_time')->nullable();
            
            // Friday
            $table->enum('friday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('friday_opening_time')->nullable();
            $table->time('friday_closing_time')->nullable();
            
            // Saturday
            $table->enum('saturday_status', ['off', 'default', 'custom'])->default('default');
            $table->time('saturday_opening_time')->nullable();
            $table->time('saturday_closing_time')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to ensure one working hours record per entity
            $table->unique(['workable_id', 'workable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};

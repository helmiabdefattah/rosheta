<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('bonus_points', function (Blueprint $table) {
			$table->id();
			$table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
			$table->enum('source_type', ['order', 'nurse_visit', 'welcome']);
			$table->unsignedBigInteger('source_id')->default(0);
			$table->unsignedInteger('points');
			$table->timestamps();

			$table->unique(['client_id', 'source_type', 'source_id'], 'bonus_points_unique_source');
			$table->index(['client_id']);
			$table->index(['source_type']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('bonus_points');
	}
};





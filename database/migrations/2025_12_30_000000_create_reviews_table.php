<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('reviews', function (Blueprint $table) {
			$table->id();
			$table->morphs('reviewable'); // reviewable_type, reviewable_id
			$table->unsignedBigInteger('client_id')->nullable();
			$table->unsignedBigInteger('offer_id')->nullable();
			$table->unsignedTinyInteger('rating'); // 1..5
			$table->text('comment')->nullable();
			$table->timestamps();

			$table->index(['client_id']);
			$table->index(['offer_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('reviews');
	}
};



<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('nurses', function (Blueprint $table) {
			$table->id();
			$table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
			$table->enum('gender', ['male', 'female'])->nullable();
			$table->date('date_of_birth')->nullable();
			$table->text('address')->nullable();
			$table->enum('qualification', ['bachelor', 'diploma', 'technical_institute'])->nullable();
			$table->string('education_place')->nullable();
			$table->unsignedSmallInteger('graduation_year')->nullable();
			$table->unsignedTinyInteger('years_of_experience')->nullable();
			$table->string('current_workplace')->nullable();
			$table->json('certifications')->nullable();
			$table->json('skills')->nullable();
			$table->timestamps();

			$table->index(['gender']);
			$table->index(['graduation_year']);
			$table->index(['years_of_experience']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('nurses');
	}
};



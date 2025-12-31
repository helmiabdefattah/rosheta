<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nurse extends Model
{
	use HasFactory;

	protected $fillable = [
		'client_id',
		'gender',
		'date_of_birth',
		'address',
		'qualification',
		'education_place',
		'graduation_year',
		'years_of_experience',
		'current_workplace',
		'certifications',
		'skills',
	];

	protected $casts = [
		'date_of_birth' => 'date',
		'certifications' => 'array',
		'skills' => 'array',
	];

	public function client(): BelongsTo
	{
		return $this->belongsTo(Client::class);
	}
}



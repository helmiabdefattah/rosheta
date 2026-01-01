<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseOffer extends Model
{
	use HasFactory;

	protected $table = 'nurse_offers';

	protected $fillable = [
		'home_nurse_request_id',
		'nurse_id',
		'price',
		'notes',
		'status',
	];

	public function request(): BelongsTo
	{
		return $this->belongsTo(HomeNurseRequest::class, 'home_nurse_request_id');
	}

	public function nurse(): BelongsTo
	{
		return $this->belongsTo(Nurse::class);
	}
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseVisit extends Model
{
	use HasFactory;

	protected $table = 'nurse_visits';

	protected $fillable = [
		'home_nurse_request_id',
		'nurse_id',
		'visit_datetime',
		'status',
		'notes',
	];

	protected $casts = [
		'visit_datetime' => 'datetime',
	];

	public function request(): BelongsTo
	{
		return $this->belongsTo(HomeNurseRequest::class, 'home_nurse_request_id');
	}

	public function nurse(): BelongsTo
	{
		return $this->belongsTo(Nurse::class, 'nurse_id');
	}
}



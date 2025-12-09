<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'area_id',
		'name',
		'phone',
		'email',
		'logo',
		'address',
		'location',
		'lat',
		'lng',
		'license_number',
		'manager_name',
		'manager_license',
		'opening_time',
		'closing_time',
		'is_active',
		'notes',
	];

	protected $casts = [
		'lat' => 'decimal:8',
		'lng' => 'decimal:8',
		'is_active' => 'boolean',
		'location' => 'array',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function area(): BelongsTo
	{
		return $this->belongsTo(Area::class);
	}

	public function medicalTests(): HasMany
	{
		return $this->hasMany(MedicalTest::class, 'laboratory_id');
	}

	public function offers(): HasMany
	{
		return $this->hasMany(MedicalTestOffer::class);
	}
}



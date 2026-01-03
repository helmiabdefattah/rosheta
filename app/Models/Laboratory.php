<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Laboratory extends Model implements HasMedia
{
	use HasFactory, InteractsWithMedia;

	protected $fillable = [
		'user_id',
		'area_id',
		'name',
		'phone',
		'email',
		'type',
		'logo',
		'address',
		'location',
		'lat',
		'lng',
		'license_number',
		'manager_name',
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
		return $this->hasMany(Offer::class,'laboratory_id');
	}

	public function testPrices(): HasMany
	{
		return $this->hasMany(LaboratoryTestPrice::class);
	}

	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('logo')
			->singleFile()
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
		
		$this->addMediaCollection('manager_license')
			->singleFile()
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);
	}

	public function registerMediaConversions(?Media $media = null): void
	{
		$this->addMediaConversion('thumb')
			->width(200)
			->height(200)
			->sharpen(10)
			->performOnCollections('logo');
	}

	public function reviews()
	{
		return $this->morphMany(Review::class, 'reviewable');
	}

	public function workingHours()
	{
		return $this->morphOne(WorkingHours::class, 'workable');
	}
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusPoint extends Model
{
	use HasFactory;

	protected $table = 'bonus_points';

	protected $fillable = [
		'client_id',
		'source_type',
		'source_id',
		'points',
		'used',
		'status',
	];

	public function client()
	{
		return $this->belongsTo(Client::class);
	}

	protected $casts = [
		'used' => 'boolean',
	];

	/**
	 * Award bonus points once per (client, source_type, source_id).
	 * Returns the BonusPoint model if created or found, null if invalid arguments.
	 */
	public static function awardUnique(int $clientId, string $sourceType, int $sourceId, int $points): ?self
	{
		if ($clientId <= 0 || $points <= 0) {
			return null;
		}

		$sourceId = $sourceId > 0 ? $sourceId : 0;

		return static::firstOrCreate(
			[
				'client_id' => $clientId,
				'source_type' => $sourceType,
				'source_id' => $sourceId,
			],
			[
				'points' => $points,
			]
		);
	}
}



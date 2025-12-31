<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
	use HasFactory;

	protected $fillable = [
		'reviewable_type',
		'reviewable_id',
		'client_id',
		'offer_id',
		'rating',
		'comment',
	];

	public function reviewable(): MorphTo
	{
		return $this->morphTo();
	}

	public function client(): BelongsTo
	{
		return $this->belongsTo(Client::class);
	}

	public function offer(): BelongsTo
	{
		return $this->belongsTo(Offer::class);
	}
}



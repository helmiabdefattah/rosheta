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
        'nurse_offer_id',
        'visit_datetime',
        'status',
        'paid',
        'notes',
    ];

    protected $casts = [
        'visit_datetime' => 'datetime',
			'paid' => 'boolean',
    ];

	protected static function booted(): void
	{
		static::updated(function (self $visit) {
			// When a visit becomes paid, award points based on offer.visit_price
			if ($visit->isDirty('paid') && (bool)$visit->paid === true) {
				$clientId = (int) ($visit->request?->client_id ?? 0);
				$visitPrice = (float) ($visit->offer?->visit_price ?? 0);
				$points = (int) round($visitPrice);
				if ($clientId > 0 && $points > 0) {
					\App\Models\BonusPoint::awardUnique(
						clientId: $clientId,
						sourceType: 'nurse_visit',
						sourceId: (int) $visit->id,
						points: $points
					);
				}
			}
		});
	}

    public function request(): BelongsTo
    {
        return $this->belongsTo(HomeNurseRequest::class, 'home_nurse_request_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

		public function offer(): BelongsTo
		{
			return $this->belongsTo(NurseOffer::class, 'nurse_offer_id');
		}

    // Optional: Add status scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('visit_datetime', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('visit_datetime', '<=', now());
    }
    public function review()
    {
        return $this->morphOne(Review::class, 'reviewable');
    }
}

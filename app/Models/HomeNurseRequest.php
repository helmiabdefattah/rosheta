<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class HomeNurseRequest extends Model
{
	use HasFactory;

	protected $fillable = [
		'client_id',
		'address_id',
		'nurse_id',
		'service_type',
		'medical_notes',
		'visits_count',
		'visit_frequency',
		'visit_start_date',
		'visit_time',
		'needs_overnight',
		'overnight_days',
		'total_price',
		'status',
		'payment_status',
	];

	protected $casts = [
		'needs_overnight' => 'boolean',
		'visit_start_date' => 'date',
	];

	protected static function booted(): void
	{
		static::created(function (self $request) {
			$request->scheduleVisits();
		});
	}

	public function client(): BelongsTo
	{
		return $this->belongsTo(Client::class, 'client_id');
	}

	public function address(): BelongsTo
	{
		return $this->belongsTo(ClientAddress::class, 'address_id');
	}

	public function nurse(): BelongsTo
	{
		return $this->belongsTo(Nurse::class, 'nurse_id');
	}

	public function visits(): HasMany
	{
		return $this->hasMany(NurseVisit::class, 'home_nurse_request_id');
	}

	public function offer(): HasOne
	{
		return $this->hasOne(NurseOffer::class, 'home_nurse_request_id')->where('status', 'accepted');
	}

	public function review(): MorphOne
	{
		return $this->morphOne(Review::class, 'reviewable');
	}

	public function scheduleVisits(): void
	{
		$count = (int) $this->visits_count;
		if ($count <= 0) {
			return;
		}

        $start = Carbon::parse(Carbon::parse($this->visit_start_date)->format('Y-m-d') . ' ' . $this->visit_time);
		$current = $start->copy();

		for ($i = 0; $i < $count; $i++) {
			$this->visits()->create([
				'nurse_id' => $this->nurse_id,
				'visit_datetime' => $current->copy(),
				'status' => 'scheduled',
			]);

			switch ($this->visit_frequency) {
				case 'daily':
					$current = $current->copy()->addDay();
					break;
				case 'every_two_days':
					$current = $current->copy()->addDays(2);
					break;
				case 'once_weekly':
					$current = $current->copy()->addWeek();
					break;
				case 'twice_weekly':
					$current = $current->copy()->addDays(($i % 2) ? 4 : 3);
					break;
				default:
					$current = $current->copy()->addWeek();
			}
		}

		$this->forceFill(['status' => 'scheduled'])->save();
	}
}



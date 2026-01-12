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
		'preferred_gender',
		'medical_notes',
		'patient_age',
		'medical_condition',
		'visits_count',
		'visit_frequency',
		'custom_visit_days',
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
		'custom_visit_days' => 'array',
	];

//	protected static function booted(): void
//	{
//		static::created(function (self $request) {
//			$request->scheduleVisits();
//		});
//	}

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

	public function offers(): HasMany
	{
		return $this->hasMany(NurseOffer::class, 'home_nurse_request_id');
	}

	public function scheduleVisits(): void
	{
		$count = (int) $this->visits_count;
		if ($count <= 0) {
			return;
		}

        $start = Carbon::parse(Carbon::parse($this->visit_start_date)->format('Y-m-d') . ' ' . $this->visit_time);
		$current = $start->copy();

		// If custom days are selected, schedule based on those days
		if ($this->visit_frequency === 'custom' && !empty($this->custom_visit_days)) {
			$customDays = $this->custom_visit_days; // Array of day numbers (0=Sunday, 1=Monday, etc.)
			$scheduledCount = 0;
			$weeksToCheck = ceil($count / count($customDays)) + 1; // Add buffer weeks
			
			for ($week = 0; $week < $weeksToCheck && $scheduledCount < $count; $week++) {
				foreach ($customDays as $dayOfWeek) {
					if ($scheduledCount >= $count) break;
					
					// Calculate the date for this day of week in this week
					$targetDate = $start->copy()->startOfWeek()->addDays($dayOfWeek);
					if ($week > 0) {
						$targetDate->addWeeks($week);
					}
					
					// Only schedule if the date is >= start date
					if ($targetDate->format('Y-m-d') >= $start->format('Y-m-d')) {
						$visitDateTime = Carbon::parse($targetDate->format('Y-m-d') . ' ' . $this->visit_time);
						$this->visits()->create([
							'nurse_id' => $this->nurse_id,
							'visit_datetime' => $visitDateTime,
							'status' => 'scheduled',
						]);
						$scheduledCount++;
					}
				}
			}
		} else {
			// Standard scheduling logic
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
					case 'weekly':
						$current = $current->copy()->addWeek();
						break;
					default:
						$current = $current->copy()->addWeek();
				}
			}
		}

		$this->forceFill(['status' => 'pending'])->save();
	}

	/**
	 * Get translated service type.
	 */
	public function getTranslatedServiceType(): string
	{
		$translations = [
			'Daily Basic Care Assistance' => [
				'en' => 'Daily Basic Care Assistance',
				'ar' => 'مساعدة الرعاية الأساسية اليومية'
			],
			'Health Monitoring Services' => [
				'en' => 'Health Monitoring Services',
				'ar' => 'خدمات مراقبة الصحة'
			],
			'Post-Surgery Recovery Care' => [
				'en' => 'Post-Surgery Recovery Care',
				'ar' => 'رعاية ما بعد الجراحة'
			],
			'Elderly & Senior Care' => [
				'en' => 'Elderly & Senior Care',
				'ar' => 'رعاية كبار السن'
			],
			'Maternal & Newborn Support' => [
				'en' => 'Maternal & Newborn Support',
				'ar' => 'دعم الأمهات والمواليد الجدد'
			],
			'Pediatric Nursing Services' => [
				'en' => 'Pediatric Nursing Services',
				'ar' => 'خدمات التمريض للأطفال'
			],
			'Chronic Disease Management' => [
				'en' => 'Chronic Disease Management',
				'ar' => 'إدارة الأمراض المزمنة'
			],
			'Emergency & Urgent Care' => [
				'en' => 'Emergency & Urgent Care',
				'ar' => 'الرعاية الطارئة والعاجلة'
			],
			'Injury & Trauma Care' => [
				'en' => 'Injury & Trauma Care',
				'ar' => 'رعاية الإصابات والصدمات'
			],
			'Respite & Family Support' => [
				'en' => 'Respite & Family Support',
				'ar' => 'دعم الأسرة والراحة'
			],
			'Medical Equipment Assistance' => [
				'en' => 'Medical Equipment Assistance',
				'ar' => 'مساعدة المعدات الطبية'
			],
			'Home Testing & Sample Collection' => [
				'en' => 'Home Testing & Sample Collection',
				'ar' => 'الفحوصات المنزلية وجمع العينات'
			],
			'Nutrition & Feeding Support' => [
				'en' => 'Nutrition & Feeding Support',
				'ar' => 'دعم التغذية والتغذية'
			],
			'Wound Care & Dressing Changes' => [
				'en' => 'Wound Care & Dressing Changes',
				'ar' => 'رعاية الجروح وتغيير الضمادات'
			],
			'Pain Management Services' => [
				'en' => 'Pain Management Services',
				'ar' => 'خدمات إدارة الألم'
			],
			'Personal Hygiene Assistance' => [
				'en' => 'Personal Hygiene Assistance',
				'ar' => 'مساعدة النظافة الشخصية'
			],
			'Rehabilitation & Mobility Support' => [
				'en' => 'Rehabilitation & Mobility Support',
				'ar' => 'دعم إعادة التأهيل والحركة'
			],
			'Palliative & Comfort Care' => [
				'en' => 'Palliative & Comfort Care',
				'ar' => 'الرعاية التلطيفية والراحة'
			],
			'Health Education & Training' => [
				'en' => 'Health Education & Training',
				'ar' => 'التعليم الصحي والتدريب'
			],
			'General Nursing Consultation' => [
				'en' => 'General Nursing Consultation',
				'ar' => 'استشارة تمريضية عامة'
			],
		];

		$locale = app()->getLocale();
		return $translations[$this->service_type][$locale] ?? $this->service_type;
	}
    public function scopeRequestsList($query, Nurse $nurse = null)
    {
        return $query
            ->with([
                'client.governorate',
                'client.city',
                'address.area.city.governorate',
                'offers'
            ])
            ->where('status', 'pending')
            ->when($nurse, function ($q) use ($nurse) {
                // Exclude requests already offered by this nurse
                $q->whereDoesntHave('offers', function ($offerQuery) use ($nurse) {
                    $offerQuery->where('nurse_id', $nurse->id);
                });
                // Gender preference filter
                $q->where(function ($genderQuery) use ($nurse) {
                    $genderQuery
                        ->whereNull('preferred_gender')
                        ->orWhere('preferred_gender', $nurse->gender);
                });
            })
            ->latest();
    }

}



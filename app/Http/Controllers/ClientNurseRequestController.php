<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\HomeNurseRequest;
use App\Models\NurseOffer;
use App\Models\Area;
use App\Models\NurseVisit;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewClientRequestNotification;
use App\Notifications\NurseOfferAcceptedNotification;
use App\Notifications\NurseOfferRejectedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClientNurseRequestController extends Controller
{
	/**
	 * Get available service types.
	 */
	private function getServiceTypes(): array
	{
		return [
			'Daily Basic Care Assistance',
			'Health Monitoring Services',
			'Post-Surgery Recovery Care',
			'Elderly & Senior Care',
			'Maternal & Newborn Support',
			'Pediatric Nursing Services',
			'Chronic Disease Management',
			'Emergency & Urgent Care',
			'Injury & Trauma Care',
			'Respite & Family Support',
			'Medical Equipment Assistance',
			'Home Testing & Sample Collection',
			'Nutrition & Feeding Support',
			'Wound Care & Dressing Changes',
			'Pain Management Services',
			'Personal Hygiene Assistance',
			'Rehabilitation & Mobility Support',
			'Palliative & Comfort Care',
			'Health Education & Training',
			'General Nursing Consultation',
		];
	}

	/**
	 * Get service type translation.
	 */
	private function getServiceTypeTranslation(string $serviceType): string
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
		return $translations[$serviceType][$locale] ?? $serviceType;
	}

	/**
	 * List client's home nurse requests.
	 */
    public function index()
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 403);

        $requests = HomeNurseRequest::with([
            'address.area.city.governorate',
            'offers.nurse.user'  // <--- this gives you the nurse data and user info
        ])
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $allAreaIds = collect($requests->items())
            ->flatMap(fn($req) => $req->offers->flatMap(fn($offer) => $offer->nurse?->area_ids ?? []))
            ->filter()
            ->unique()
            ->values();

        $areaMap = $allAreaIds->isNotEmpty()
            ? Area::with('city.governorate')->whereIn('id', $allAreaIds)->get()->keyBy('id')
            : collect();

        return view('client.nurse-requests.index', [
            'requests' => $requests,
            'areaMap' => $areaMap,
        ]);
    }

	/**
	 * Show create form.
	 */
	public function create()
	{
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$addresses = $client->addresses()->with(['city', 'area'])->orderByDesc('id')->get();
		$serviceTypes = $this->getServiceTypes();
		$serviceTypesWithTranslations = array_map(function($type) {
			return [
				'value' => $type,
				'label' => $this->getServiceTypeTranslation($type)
			];
		}, $serviceTypes);

		return view('client.nurse-requests.create', compact('addresses', 'serviceTypes', 'serviceTypesWithTranslations'));
	}

	/**
	 * Store new home nurse request; visits are auto-scheduled by model event.
	 */
	public function store(Request $request)
	{
//        dd($request);
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$serviceTypes = $this->getServiceTypes();
		$validated = $request->validate([
			'service_type' => ['required', 'string', Rule::in($serviceTypes)],
			'preferred_gender' => 'nullable|in:male,female',
			'medical_notes' => 'nullable|string|max:2000',
			'patient_age' => 'nullable|integer|min:0|max:150',
			'medical_condition' => 'nullable|string|max:2000',
			'address_id' => 'nullable|exists:client_addresses,id',
			'visits_count' => 'required|integer|min:1|max:60',
			'visit_frequency' => [
				Rule::requiredIf(function () use ($request) {
					return $request->input('visits_count', 1) > 1;
				}),
				Rule::in(['daily', 'every_two_days', 'weekly', 'custom']),
			],
			'custom_visit_days' => [
				'nullable',
				'array',
				'required_if:visit_frequency,custom',
				function ($attribute, $value, $fail) {
					if (is_array($value)) {
						$days = array_map('intval', $value);
						$uniqueDays = array_unique($days);
						
						// Must select at least one day
						if (count($uniqueDays) === 0) {
							$fail(__('Please select at least one day.'));
							return;
						}
						
						// Cannot select all 7 days
						if (count($uniqueDays) === 7) {
							$fail(__('Cannot select all days of the week.'));
							return;
						}
						
						// Cannot select consecutive days
						sort($uniqueDays);
						for ($i = 0; $i < count($uniqueDays); $i++) {
							$current = $uniqueDays[$i];
							$next = $uniqueDays[($i + 1) % count($uniqueDays)];
							$diff = ($next - $current + 7) % 7;
							
							if ($diff === 1) {
								$fail(__('Cannot select two consecutive days.'));
								return;
							}
						}
					}
				},
			],
			'custom_visit_days.*' => 'integer|min:0|max:6',
			'visit_start_date' => 'required|date|after_or_equal:today',
			'visit_time' => 'required|date_format:H:i',
			'needs_overnight' => 'sometimes|boolean',
			'overnight_days' => 'nullable|integer|min:1|max:30',
			'total_price' => 'nullable|numeric|min:0',
		]);

		// If visits_count is 1, set visit_frequency to null
		if ($validated['visits_count'] == 1) {
			$validated['visit_frequency'] = null;
			$validated['custom_visit_days'] = null;
		}

        $requestModel = HomeNurseRequest::create([
			'client_id' => $client->id,
			'address_id' => $validated['address_id'] ?? null,
			'service_type' => $validated['service_type'],
			'preferred_gender' => $validated['preferred_gender'] ?? null,
			'medical_notes' => $validated['medical_notes'] ?? null,
			'patient_age' => $validated['patient_age'] ?? null,
			'medical_condition' => $validated['medical_condition'] ?? null,
			'visits_count' => $validated['visits_count'],
            'visit_frequency' => $validated['visit_frequency'] ?? null,
			'custom_visit_days' => $validated['custom_visit_days'] ?? null,
            'visit_start_date' => $validated['visit_start_date'],
			'visit_time' => $validated['visit_time'],
			'needs_overnight' => (bool)($validated['needs_overnight'] ?? false),
			'overnight_days' => $validated['overnight_days'] ?? null,
			'total_price' => $validated['total_price'] ?? null,
			'status' => 'pending',
			'payment_status' => 'pending',
		]);

        // Notify related nurses
        try {
            $requestModel->load(['address', 'client']);
            if ( true || ($requestModel->address && $requestModel->address->area_id) ) {
                $areaId = $requestModel->address->area_id;
                $nurseUsers = User::whereNotNull('nurse_id')
                    ->whereHas('nurse', function($q) use($areaId) {
                        // $q->whereJsonContains('area_ids', (string)$areaId)
                          $q->where('status', 'active');
                    })->get();
                
                if ($nurseUsers->count() > 0) {
                    Notification::send($nurseUsers, new NewClientRequestNotification($requestModel));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Notification failed for new nurse request: ' . $e->getMessage());
        }

        return redirect()
            ->route('client.nurse-requests.show', $requestModel)
            ->with('success', __('Request submitted successfully.'));
	}

	/**
	 * Show a request and its scheduled visits.
	 */
	public function show(HomeNurseRequest $home_nurse_request)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client && $home_nurse_request->client_id === $client->id, 403);

		$home_nurse_request->load(['address.city', 'address.area', 'nurse.client', 'visits' => function ($q) {
			$q->orderBy('visit_datetime');
		}]);

		return view('client.nurse-requests.show', ['request' => $home_nurse_request]);
	}

	/**
	 * Edit form.
	 */
	public function edit(HomeNurseRequest $home_nurse_request)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client && $home_nurse_request->client_id === $client->id, 403);

		$addresses = $client->addresses()->with(['city', 'area'])->orderByDesc('id')->get();
		$serviceTypes = $this->getServiceTypes();
		$serviceTypesWithTranslations = array_map(function($type) {
			return [
				'value' => $type,
				'label' => $this->getServiceTypeTranslation($type)
			];
		}, $serviceTypes);

		return view('client.nurse-requests.edit', [
			'request' => $home_nurse_request,
			'addresses' => $addresses,
			'serviceTypes' => $serviceTypes,
			'serviceTypesWithTranslations' => $serviceTypesWithTranslations,
		]);
	}

	/**
	 * Update request; if scheduling fields changed, regenerate remaining scheduled visits.
	 */
	public function update(Request $request, HomeNurseRequest $home_nurse_request)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client && $home_nurse_request->client_id === $client->id, 403);

		$serviceTypes = $this->getServiceTypes();
		$validated = $request->validate([
			'service_type' => ['required', 'string', Rule::in($serviceTypes)],
			'preferred_gender' => 'nullable|in:male,female',
			'medical_notes' => 'nullable|string|max:2000',
			'patient_age' => 'nullable|integer|min:0|max:150',
			'medical_condition' => 'nullable|string|max:2000',
			'address_id' => 'nullable|exists:client_addresses,id',
			'visits_count' => 'required|integer|min:1|max:60',
			'visit_frequency' => [
				'required',
				Rule::in(['daily', 'every_two_days', 'weekly', 'custom']),
			],
			'custom_visit_days' => [
				'nullable',
				'array',
				'required_if:visit_frequency,custom',
				function ($attribute, $value, $fail) {
					if (is_array($value)) {
						$days = array_map('intval', $value);
						$uniqueDays = array_unique($days);
						
						// Must select at least one day
						if (count($uniqueDays) === 0) {
							$fail(__('Please select at least one day.'));
							return;
						}
						
						// Cannot select all 7 days
						if (count($uniqueDays) === 7) {
							$fail(__('Cannot select all days of the week.'));
							return;
						}
						
						// Cannot select consecutive days
						sort($uniqueDays);
						for ($i = 0; $i < count($uniqueDays); $i++) {
							$current = $uniqueDays[$i];
							$next = $uniqueDays[($i + 1) % count($uniqueDays)];
							$diff = ($next - $current + 7) % 7;
							
							if ($diff === 1) {
								$fail(__('Cannot select two consecutive days.'));
								return;
							}
						}
					}
				},
			],
			'custom_visit_days.*' => 'integer|min:0|max:6',
			'visit_start_date' => 'required|date|after_or_equal:today',
			'visit_time' => [
				'required',
				'date_format:H:i',
				function ($attribute, $value, $fail) {
					if ($value) {
						$parts = explode(':', $value);
						if (count($parts) === 2) {
							$minute = (int)$parts[1];
							if (!in_array($minute, [0, 15, 30, 45])) {
								$fail(__('Minutes must be 00, 15, 30, or 45.'));
							}
						}
					}
				},
			],
			'needs_overnight' => 'sometimes|boolean',
			'overnight_days' => 'nullable|integer|min:1|max:30',
			'total_price' => 'nullable|numeric|min:0',
		]);

		// If visits_count is 1, set visit_frequency to null
		if ($validated['visits_count'] == 1) {
			$validated['visit_frequency'] = null;
			$validated['custom_visit_days'] = null;
		}

		$dirtySchedule = (
			$home_nurse_request->visits_count != $validated['visits_count'] ||
			$home_nurse_request->visit_frequency !== ($validated['visit_frequency'] ?? null) ||
			$home_nurse_request->custom_visit_days != ($validated['custom_visit_days'] ?? null) ||
			$home_nurse_request->visit_start_date?->format('Y-m-d') !== $validated['visit_start_date'] ||
			$home_nurse_request->visit_time !== $validated['visit_time'] ||
			(bool)$home_nurse_request->needs_overnight !== (bool)($validated['needs_overnight'] ?? false) ||
			(int)($home_nurse_request->overnight_days ?? 0) !== (int)($validated['overnight_days'] ?? 0)
		);

		$home_nurse_request->update([
			'address_id' => $validated['address_id'] ?? null,
			'service_type' => $validated['service_type'],
			'preferred_gender' => $validated['preferred_gender'] ?? null,
			'medical_notes' => $validated['medical_notes'] ?? null,
			'patient_age' => $validated['patient_age'] ?? null,
			'medical_condition' => $validated['medical_condition'] ?? null,
			'visits_count' => $validated['visits_count'],
			'visit_frequency' => $validated['visit_frequency'] ?? null,
			'custom_visit_days' => $validated['custom_visit_days'] ?? null,
			'visit_start_date' => $validated['visit_start_date'],
			'visit_time' => $validated['visit_time'],
			'needs_overnight' => (bool)($validated['needs_overnight'] ?? false),
			'overnight_days' => $validated['overnight_days'] ?? null,
			'total_price' => $validated['total_price'] ?? null,
		]);

		if ($dirtySchedule) {
			$home_nurse_request->visits()->where('status', 'scheduled')->delete();
			$home_nurse_request->scheduleVisits();
		}

		return redirect()
			->route('client.nurse-requests.show', $home_nurse_request)
			->with('success', __('Request updated successfully.'));
	}

	/**
	 * Accept a nurse offer.
	 */
    public function acceptOffer(NurseOffer $nurse_offer)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $nurse_offer->request && $nurse_offer->request->client_id === $client->id, 403);

        if ($nurse_offer->status === 'accepted') {
            return back()->with('success', __('Offer already accepted.'));
        }

        DB::transaction(function () use ($nurse_offer) {
            // Reject other offers for the same request
            NurseOffer::where('home_nurse_request_id', $nurse_offer->home_nurse_request_id)
                ->where('id', '!=', $nurse_offer->id)
                ->update(['status' => 'rejected']);

            // Accept this one
            $nurse_offer->update(['status' => 'accepted']);

            // Link nurse to request
            $req = $nurse_offer->request()->with('visits')->first();
            $req->update([
                'nurse_id' => $nurse_offer->nurse_id,
                'status' => 'scheduled',
            ]);

            // Assign nurse to any unassigned scheduled visits
            $req->visits()->where('status', 'scheduled')->whereNull('nurse_id')
                ->update([
                    'nurse_id' => $nurse_offer->nurse_id,
                    'nurse_offer_id' => $nurse_offer->id,
                ]);

            // Finally: schedule visits for this offer if not already created
            $nurse_offer->scheduleVisits();
        });

        // Load relationships and send notification to nurse
        try {
            $nurse_offer->refresh();
            $nurse_offer->load(['request.client', 'nurse.user']);
            if ($nurse_offer->nurse && $nurse_offer->nurse->user) {
                $nurseUser = $nurse_offer->nurse->user;
                $nurseUser->notify(new NurseOfferAcceptedNotification($nurse_offer));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to send nurse offer accepted notification: ' . $e->getMessage());
        }

        return back()->with('success', __('Offer accepted successfully.'));
    }
	/**
	 * Reject a nurse offer.
	 */
	public function rejectOffer(NurseOffer $nurse_offer)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client && $nurse_offer->request && $nurse_offer->request->client_id === $client->id, 403);

		if ($nurse_offer->status !== 'pending') {
			return back()->with('success', __('Offer already processed.'));
		}

		$nurse_offer->update(['status' => 'rejected']);

		// Load relationships and send notification to nurse
		try {
			$nurse_offer->refresh();
			$nurse_offer->load(['request.client', 'nurse.user']);
			if ($nurse_offer->nurse && $nurse_offer->nurse->user) {
				$nurseUser = $nurse_offer->nurse->user;
				$nurseUser->notify(new NurseOfferRejectedNotification($nurse_offer));
			}
		} catch (\Exception $e) {
			// Log error but don't fail the request
			Log::error('Failed to send nurse offer rejected notification: ' . $e->getMessage());
		}

		return back()->with('success', __('Offer rejected.'));
	}
    public function visitsList()
    {
        // Get the currently logged-in client
        $client = Auth::guard('client')->user();
        abort_unless($client, 403);

        // Fetch visits related to this client's requests
        $visits = NurseVisit::with(['request', 'request.client', 'nurse.user','review','offer'])
            ->whereHas('request', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->orderByDesc('visit_datetime')
            ->paginate(10); // You can adjust pagination as needed

        return view('client.visits.index', [
            'visits' => $visits,
        ]);
    }
    public function rateVisit(Request $request, NurseVisit $visit)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $visit->request && $visit->request->client_id === $client->id, 403);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Create review for this visit (or update if already exists)
        $review = Review::updateOrCreate(
            [
                'reviewable_type' => NurseVisit::class,
                'reviewable_id' => $visit->id,
                'client_id' => $client->id,
            ],
            [
                'offer_id' => $visit->offer_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return back()->with('success', __('Rating submitted successfully.'));
    }
}



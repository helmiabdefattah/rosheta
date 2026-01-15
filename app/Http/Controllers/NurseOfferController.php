<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\HomeNurseRequest;
use App\Models\NurseOffer;
use App\Models\Nurse;
use App\Models\NurseVisit;
use App\Notifications\NurseOfferCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NurseOfferController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->nurse, 403);

        $nurse = $user->nurse;

        // Nurse offers
        $offers = NurseOffer::with('request.client')
            ->where('nurse_id', $nurse->id)
            ->latest()
            ->paginate(10);

        // ✅ Nurse visits
        $visits = NurseVisit::with('request.client')
            ->where('nurse_id', $nurse->id)
            ->orderBy('visit_datetime')
            ->paginate(10);

        // (Optional) nurses list — if you really need it here
        $nurses = Nurse::with('user')->orderByDesc('id')->paginate(15);

        $allAreaIds = collect($nurses->items())
            ->flatMap(fn ($n) => is_array($n->area_ids) ? $n->area_ids : [])
            ->filter()
            ->unique()
            ->values();

        $areaMap = $allAreaIds->isNotEmpty()
            ? Area::with('city.governorate')->whereIn('id', $allAreaIds)->get()->keyBy('id')
            : collect();

        return view('nurse.offers.index', compact(
            'offers',
            'visits',
            'nurse',
            'areaMap'
        ));
    }
	public function create(Request $request)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id, 403);

		$preselectedRequestId = (int) $request->query('request_id');

		// Available requests to offer on: pending, and no existing offer by this nurse
		$existingOfferedIds = NurseOffer::where('nurse_id', $user->nurse_id)->pluck('home_nurse_request_id')->all();
		$availableRequests = HomeNurseRequest::with(['client', 'address.area.city'])
			->where('status', 'pending')
			->whereNotIn('id', $existingOfferedIds)
			->orderByDesc('created_at')
			->limit(50)
			->get();
        $nurses = Nurse::with('user')->orderByDesc('id')->paginate(15);

        $allAreaIds = collect($nurses->items())
            ->flatMap(fn ($n) => is_array($n->area_ids) ? $n->area_ids : [])
            ->filter()
            ->unique()
            ->values();

        $areaMap = $allAreaIds->isNotEmpty()
            ? Area::with('city.governorate')->whereIn('id', $allAreaIds)->get()->keyBy('id')
            : collect();
		return view('nurse.offers.create', [
            'areaMap'=>$areaMap,
			'availableRequests' => $availableRequests,
			'preselectedRequestId' => $preselectedRequestId ?: null,
		]);
	}

	public function store(Request $request)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id, 403);

		$validated = $request->validate([
			'home_nurse_request_id' => ['required', 'exists:home_nurse_requests,id'],
			'notes' => ['nullable', 'string', 'max:2000'],
			'visit_period' => ['required', Rule::in(['daily', 'every_two_days', 'weekly', 'custom'])],
			'custom_visit_days' => [
				'nullable',
				'array',
				'required_if:visit_period,custom',
				function ($attribute, $value, $fail) {
					if (is_array($value)) {
						$days = array_map('intval', $value);
						$uniqueDays = array_unique($days);
						
						if (count($uniqueDays) === 0) {
							$fail(__('Please select at least one day.'));
							return;
						}
						
						if (count($uniqueDays) === 7) {
							$fail(__('Cannot select all days of the week.'));
							return;
						}
						
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
			'visit_start_time' => [
				'nullable',
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
			'visit_duration' => ['nullable', 'integer', 'min:1', 'max:24'],
			'visits_count' => ['required', 'integer', 'min:1', 'max:60'],
			'visit_price' => ['required', 'numeric', 'min:0'],
			'total_price' => ['nullable', 'numeric', 'min:0'],
		]);

		// Prevent duplicate offers by same nurse
		$duplicate = NurseOffer::where('home_nurse_request_id', $validated['home_nurse_request_id'])
			->where('nurse_id', $user->nurse_id)
			->exists();
		if ($duplicate) {
			return back()->withErrors(['home_nurse_request_id' => __('You already created an offer for this request.')])->withInput();
		}

		$total = $validated['total_price'] ?? (float)$validated['visit_price'] * (int)$validated['visits_count'];

		$offer = NurseOffer::create([
			'home_nurse_request_id' => $validated['home_nurse_request_id'],
			'nurse_id' => $user->nurse_id,
			'notes' => $validated['notes'] ?? null,
			'visit_period' => $validated['visit_period'],
			'custom_visit_days' => $validated['custom_visit_days'] ?? null,
			'visit_start_time' => $validated['visit_start_time'] ?? null,
			'visit_duration' => $validated['visit_duration'] ?? null,
			'visits_count' => $validated['visits_count'],
			'visit_price' => $validated['visit_price'],
			'total_price' => $total,
			// keep legacy 'price' in sync for existing UI
			'price' => $total,
			'status' => 'pending',
		]);

		// Load necessary relationships and send notification to client
		try {
			$offer->load(['request.client', 'nurse.user']);
			if ($offer->request && $offer->request->client) {
				$client = $offer->request->client;
				$client->notify(new NurseOfferCreatedNotification($offer));
			}
		} catch (\Exception $e) {
			// Log error but don't fail the request
			Log::error('Failed to send nurse offer notification: ' . $e->getMessage());
		}

		return redirect()->route('nurse.offers.index')->with('success', __('Offer created successfully.'));
	}

	public function edit(NurseOffer $offer)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id === $offer->nurse_id, 403);

		if ($offer->status !== 'pending') {
			return redirect()->route('nurse.offers.index')->with('error', __('Only pending offers can be edited.'));
		}

		return view('nurse.offers.edit', compact('offer'));
	}

	public function update(Request $request, NurseOffer $offer)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id === $offer->nurse_id, 403);

		if ($offer->status !== 'pending') {
			return redirect()->route('nurse.offers.index')->with('error', __('Only pending offers can be updated.'));
		}

		$validated = $request->validate([
			'notes' => ['nullable', 'string', 'max:2000'],
			'visit_period' => ['required', Rule::in(['daily', 'every_two_days', 'weekly', 'custom'])],
			'custom_visit_days' => [
				'nullable',
				'array',
				'required_if:visit_period,custom',
				function ($attribute, $value, $fail) {
					if (is_array($value)) {
						$days = array_map('intval', $value);
						$uniqueDays = array_unique($days);
						
						if (count($uniqueDays) === 0) {
							$fail(__('Please select at least one day.'));
							return;
						}
						
						if (count($uniqueDays) === 7) {
							$fail(__('Cannot select all days of the week.'));
							return;
						}
						
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
			'visit_start_time' => [
				'nullable',
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
			'visit_duration' => ['nullable', 'integer', 'min:1', 'max:24'],
			'visits_count' => ['required', 'integer', 'min:1', 'max:60'],
			'visit_price' => ['required', 'numeric', 'min:0'],
			'total_price' => ['nullable', 'numeric', 'min:0'],
		]);

		$total = $validated['total_price'] ?? (float)$validated['visit_price'] * (int)$validated['visits_count'];

		$offer->update([
			'notes' => $validated['notes'] ?? null,
			'visit_period' => $validated['visit_period'],
			'custom_visit_days' => $validated['custom_visit_days'] ?? null,
			'visit_start_time' => $validated['visit_start_time'] ?? null,
			'visit_duration' => $validated['visit_duration'] ?? null,
			'visits_count' => $validated['visits_count'],
			'visit_price' => $validated['visit_price'],
			'total_price' => $total,
			'price' => $total,
		]);

		return redirect()->route('nurse.offers.index')->with('success', __('Offer updated successfully.'));
	}

	public function destroy(NurseOffer $offer)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id === $offer->nurse_id, 403);

		if ($offer->status !== 'pending') {
			return redirect()->route('nurse.offers.index')->with('error', __('Only pending offers can be deleted.'));
		}

		$offer->delete();

		return redirect()->route('nurse.offers.index')->with('success', __('Offer deleted successfully.'));
	}
}




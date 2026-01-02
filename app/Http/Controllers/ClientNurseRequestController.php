<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\HomeNurseRequest;
use App\Models\NurseOffer;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientNurseRequestController extends Controller
{
	/**
	 * List client's home nurse requests.
	 */
	public function index()
	{
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$requests = HomeNurseRequest::with(['address', 'nurse', 'offers.nurse.client'])
			->where('client_id', $client->id)
			->orderByDesc('created_at')
			->paginate(10);

		$allAreaIds = collect($requests->items())
			->flatMap(function ($req) {
				return $req->offers->flatMap(function ($offer) {
					$ids = $offer->nurse?->area_ids ?? [];
					return is_array($ids) ? $ids : [];
				});
			})
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

		return view('client.nurse-requests.create', compact('addresses'));
	}

	/**
	 * Store new home nurse request; visits are auto-scheduled by model event.
	 */
	public function store(Request $request)
	{
//        dd($request);
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$validated = $request->validate([
			'service_type' => 'required|string|max:255',
			'medical_notes' => 'nullable|string|max:2000',
			'address_id' => 'nullable|exists:client_addresses,id',
			'visits_count' => 'required|integer|min:1|max:60',
			'visit_frequency' => 'required|in:daily,every_two_days,once_weekly,twice_weekly',
			'visit_start_date' => 'required|date|after_or_equal:today',
			'visit_time' => 'required|date_format:H:i',
			'needs_overnight' => 'sometimes|boolean',
			'overnight_days' => 'nullable|integer|min:1|max:30',
			'total_price' => 'nullable|numeric|min:0',
		]);

        $requestModel = HomeNurseRequest::create([
			'client_id' => $client->id,
			'address_id' => $validated['address_id'] ?? null,
			'service_type' => $validated['service_type'],
			'medical_notes' => $validated['medical_notes'] ?? null,
			'visits_count' => $validated['visits_count'],
            'visit_frequency' => $validated['visit_frequency'],
            'visit_start_date' => $validated['visit_start_date'],
			'visit_time' => $validated['visit_time'],
			'needs_overnight' => (bool)($validated['needs_overnight'] ?? false),
			'overnight_days' => $validated['overnight_days'] ?? null,
			'total_price' => $validated['total_price'] ?? null,
			'status' => 'pending',
			'payment_status' => 'pending',
		]);

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

		return view('client.nurse-requests.edit', [
			'request' => $home_nurse_request,
			'addresses' => $addresses,
		]);
	}

	/**
	 * Update request; if scheduling fields changed, regenerate remaining scheduled visits.
	 */
	public function update(Request $request, HomeNurseRequest $home_nurse_request)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client && $home_nurse_request->client_id === $client->id, 403);

		$validated = $request->validate([
			'service_type' => 'required|string|max:255',
			'medical_notes' => 'nullable|string|max:2000',
			'address_id' => 'nullable|exists:client_addresses,id',
			'visits_count' => 'required|integer|min:1|max:60',
			'visit_frequency' => 'required|in:daily,every_two_days,once_weekly,twice_weekly',
			'visit_start_date' => 'required|date|after_or_equal:today',
			'visit_time' => 'required|date_format:H:i',
			'needs_overnight' => 'sometimes|boolean',
			'overnight_days' => 'nullable|integer|min:1|max:30',
			'total_price' => 'nullable|numeric|min:0',
		]);

		$dirtySchedule = (
			$home_nurse_request->visits_count != $validated['visits_count'] ||
			$home_nurse_request->visit_frequency !== $validated['visit_frequency'] ||
			$home_nurse_request->visit_start_date?->format('Y-m-d') !== $validated['visit_start_date'] ||
			$home_nurse_request->visit_time !== $validated['visit_time'] ||
			(bool)$home_nurse_request->needs_overnight !== (bool)($validated['needs_overnight'] ?? false) ||
			(int)($home_nurse_request->overnight_days ?? 0) !== (int)($validated['overnight_days'] ?? 0)
		);

		$home_nurse_request->update([
			'address_id' => $validated['address_id'] ?? null,
			'service_type' => $validated['service_type'],
			'medical_notes' => $validated['medical_notes'] ?? null,
			'visits_count' => $validated['visits_count'],
			'visit_frequency' => $validated['visit_frequency'],
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

			// Link nurse to request and update scheduled visits
			$req = $nurse_offer->request()->with('visits')->first();
			$req->update(['nurse_id' => $nurse_offer->nurse_id, 'status' => 'scheduled']);
			$req->visits()->where('status', 'scheduled')->whereNull('nurse_id')->update(['nurse_id' => $nurse_offer->nurse_id]);
		});

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

		return back()->with('success', __('Offer rejected.'));
	}
}



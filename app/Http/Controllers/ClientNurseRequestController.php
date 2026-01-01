<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\HomeNurseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientNurseRequestController extends Controller
{
	/**
	 * List client's home nurse requests.
	 */
	public function index()
	{
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$requests = HomeNurseRequest::with(['address', 'nurse'])
			->where('client_id', $client->id)
			->orderByDesc('created_at')
			->paginate(10);

		return view('client.nurse-requests.index', compact('requests'));
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
}



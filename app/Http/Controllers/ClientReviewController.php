<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Offer;
use App\Models\Pharmacy;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientReviewController extends Controller
{
	public function store(Request $request)
	{
		$client = Auth::guard('client')->user();
		abort_unless($client, 403);

		$validated = $request->validate([
			'model_type' => 'required|in:laboratory,pharmacy',
			'model_id' => 'required|integer',
			'offer_id' => 'required|exists:offers,id',
			'rating' => 'required|integer|min:1|max:5',
			'comment' => 'nullable|string|max:2000',
		]);

		$offer = Offer::with('request')->findOrFail($validated['offer_id']);

		// Ensure the offer belongs to this client
		if (!$offer->request || $offer->request->client_id !== $client->id) {
			return back()->withErrors(['offer_id' => 'You are not authorized to review this offer.']);
		}

		// Determine target model and ensure it matches the offer
		if ($validated['model_type'] === 'laboratory') {
			if (!in_array($offer->request_type, [Offer::TYPE_TEST, Offer::TYPE_RADIOLOGY])) {
				return back()->withErrors(['model_type' => 'This offer is not for a laboratory service.']);
			}
			if ($offer->laboratory_id != $validated['model_id']) {
				return back()->withErrors(['model_id' => 'Laboratory does not match the offer.']);
			}
			// Ensure vendor_status = test_completed
			if ($offer->vendor_status !== 'test_completed') {
				return back()->withErrors(['offer_id' => 'You can only review after test completion.']);
			}

			$reviewableType = Laboratory::class;
		} else { // pharmacy
			if ($offer->request_type !== Offer::TYPE_MEDICINE) {
				return back()->withErrors(['model_type' => 'This offer is not for a pharmacy service.']);
			}
			if ($offer->pharmacy_id != $validated['model_id']) {
				return back()->withErrors(['model_id' => 'Pharmacy does not match the offer.']);
			}
			// Ensure vendor_status = payed
			if ($offer->vendor_status !== 'payed') {
				return back()->withErrors(['offer_id' => 'You can only review after payment.']);
			}

			$reviewableType = Pharmacy::class;
		}

		// Optional: prevent duplicate review by same client for same offer + provider
		$existing = Review::where('client_id', $client->id)
			->where('offer_id', $offer->id)
			->where('reviewable_type', $reviewableType)
			->where('reviewable_id', $validated['model_id'])
			->first();
		if ($existing) {
			return back()->withErrors(['offer_id' => 'You have already reviewed this service.']);
		}

		Review::create([
			'reviewable_type' => $reviewableType,
			'reviewable_id' => $validated['model_id'],
			'client_id' => $client->id,
			'offer_id' => $offer->id,
			'rating' => $validated['rating'],
			'comment' => $validated['comment'] ?? null,
		]);

		return back()->with('success', 'Thank you for your review.');
	}
}



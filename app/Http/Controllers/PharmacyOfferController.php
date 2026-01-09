<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyOfferController extends Controller
{
	public function index(Request $request)
	{
		$pharmacyId = Auth::user()->pharmacy_id;
		if (!$pharmacyId) {
			return redirect()->route('admin.dashboard')->with('error', __('You are not associated with any pharmacy.'));
		}
		$offers = Offer::where('pharmacy_id', $pharmacyId)
			->where('request_type', 'medicine')
			->with(['request.client'])
			->latest()
			->paginate(15);
		return view('pharmacies.offers.index', compact('offers'));
	}

	public function accepted(Request $request)
	{
		$pharmacyId = Auth::user()->pharmacy_id;
		if (!$pharmacyId) {
			return redirect()->route('admin.dashboard')->with('error', __('You are not associated with any pharmacy.'));
		}
		$offers = Offer::where('pharmacy_id', $pharmacyId)
			->where('request_type', 'medicine')
			->where('status', 'accepted')
			->with(['request.client'])
			->latest()
			->paginate(15);
		return view('pharmacies.offers.accepted', compact('offers'));
	}
}



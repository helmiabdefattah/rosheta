<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyRequestController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$pharmacy = Pharmacy::find($user->pharmacy_id);
		if (!$pharmacy) {
			return redirect()->route('admin.dashboard')->with('error', __('You are not associated with any pharmacy.'));
		}
		$q = ClientRequest::query()
			->where('type', 'medicine')
			->where('status', 'pending')
			->where(function($query) use ($pharmacy) {
				// Show requests specifically for this pharmacy
				$query->where(function($subQ) use ($pharmacy) {
					$subQ->where('model_type', 'App\Models\Pharmacy')
						 ->where('model_id', $pharmacy->id);
				})
				// OR show requests without specific provider (available to all)
				->orWhere(function($subQ) {
					$subQ->whereNull('model_type')
						 ->whereNull('model_id');
				});
			})
			->with(['client', 'address.area.city.governorate', 'lines.medicine'])
			->whereDoesntHave('offers', function ($qb) use ($pharmacy) {
				$qb->where('pharmacy_id', $pharmacy->id);
			})
			->orderByDesc('created_at');

		// Always show pending requests only for pharmacy view
		if ($request->filled('search')) {
			$term = $request->string('search');
			$q->where(function ($qb) use ($term) {
				$qb->where('id', 'like', "%{$term}%")
					->orWhereHas('client', function ($qq) use ($term) {
						$qq->where('name', 'like', "%{$term}%")
						   ->orWhere('phone_number', 'like', "%{$term}%");
					});
			});
		}

		$requests = $q->paginate(15);
		return view('pharmacies.requests.index', compact('requests', 'pharmacy'));
	}
}



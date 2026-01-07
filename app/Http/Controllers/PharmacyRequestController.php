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
		$pharmacy = Pharmacy::find($user->id ? $user->pharmacy_id : null);
		if (!$pharmacy) {
			return redirect()->route('admin.dashboard')->with('error', __('You are not associated with any pharmacy.'));
		}
		$q = ClientRequest::query()
			->where('type', 'medicine')
			->where('status', 'pending')
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



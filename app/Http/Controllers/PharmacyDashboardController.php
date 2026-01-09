<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Offer;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyDashboardController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$pharmacy = null;
		if ($user && $user->pharmacy_id) {
			$pharmacy = Pharmacy::with('user')->find($user->pharmacy_id);
		}

		if (!$pharmacy) {
			return redirect()->route('admin.dashboard')
				->with(
					'error',
					app()->getLocale() === 'ar'
						? 'أنت غير مرتبط بأي صيدلية.'
						: 'You are not associated with any pharmacy.'
				);
		}

		$requestType = 'medicine';

		$stats = [
			'total_requests' => ClientRequest::where('type', $requestType)->count(),
			'pending_requests' => ClientRequest::where('type', $requestType)->where('status', 'pending')->count(),
			'total_offers' => Offer::where('pharmacy_id', $pharmacy->id)->where('request_type', $requestType)->count(),
			'accepted_offers' => Offer::where('pharmacy_id', $pharmacy->id)->where('request_type', $requestType)->where('status', 'accepted')->count(),
			'pending_offers' => Offer::where('pharmacy_id', $pharmacy->id)->where('request_type', $requestType)->where('status', 'pending')->count(),
			'total_users' => User::where('pharmacy_id', $pharmacy->id)->count(),
		];

		$recentRequests = ClientRequest::where('type', $requestType)
			->where('status', 'pending')
			->whereDoesntHave('offers', function ($q) use ($pharmacy) {
				$q->where('pharmacy_id', $pharmacy->id);
			})
			->with([
				'client',
				'address.area.city.governorate',
				'lines' => function ($q) {
					$q->where('item_type', 'medicine')->with('medicine');
				},
				'offers' => function ($q) use ($pharmacy) {
					$q->where('pharmacy_id', $pharmacy->id);
				}
			])
			->orderBy('created_at', 'desc')
			->limit(5)
			->get();

		return view('pharmacies.dashboard', compact('pharmacy', 'stats', 'recentRequests'));
	}
}



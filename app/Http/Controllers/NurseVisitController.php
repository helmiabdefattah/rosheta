<?php

namespace App\Http\Controllers;

use App\Models\NurseVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NurseVisitController extends Controller
{
	public function updateStatus(Request $request, NurseVisit $visit)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id && $visit->nurse_id === $user->nurse_id, 403);

		$validated = $request->validate([
			'status' => ['required', Rule::in(['scheduled', 'completed', 'missed', 'cancelled'])],
		]);

		$visit->update(['status' => $validated['status']]);

		return back()->with('success', __('Visit status updated.'));
	}

	public function togglePaid(Request $request, NurseVisit $visit)
	{
		$user = Auth::user();
		abort_unless($user && $user->nurse_id && $visit->nurse_id === $user->nurse_id, 403);

		// Optional explicit value; otherwise toggle
		$paid = $request->has('paid')
			? (bool)$request->boolean('paid')
			: !$visit->paid;

		$visit->update(['paid' => $paid]);

		return back()->with('success', __('Visit payment status updated.'));
	}
}



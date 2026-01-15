<?php

namespace App\Http\Controllers;

use App\Models\NurseVisit;
use App\Notifications\NurseVisitPaidNotification;
use App\Notifications\NurseVisitStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

		// Store old status before updating
		$oldStatus = $visit->status;
		$newStatus = $validated['status'];

		// Only update and notify if status actually changed
		if ($oldStatus !== $newStatus) {
			$visit->update(['status' => $newStatus]);

			// Refresh and load necessary relationships after updating
			$visit->refresh();
			$visit->load(['request.client', 'nurse.user']);

			// Send notification to client
			try {
				if ($visit->request && $visit->request->client) {
					$client = $visit->request->client;
					$client->notify(new NurseVisitStatusUpdatedNotification($visit, $oldStatus, $newStatus));
				}
			} catch (\Exception $e) {
				// Log error but don't fail the request
				Log::error('Failed to send visit status notification: ' . $e->getMessage());
			}
		}

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

		// Store old payment status before updating
		$wasPaid = $visit->paid;

		// Only update and notify if marking as paid and wasn't already paid
		if ($paid && !$wasPaid) {
			$visit->update(['paid' => $paid]);

			// Refresh and load necessary relationships after updating
			$visit->refresh();
			$visit->load(['request.client', 'nurse.user', 'offer']);

			// Send notification to client
			try {
				if ($visit->request && $visit->request->client) {
					$client = $visit->request->client;
					$client->notify(new NurseVisitPaidNotification($visit));
				}
			} catch (\Exception $e) {
				// Log error but don't fail the request
				Log::error('Failed to send visit paid notification: ' . $e->getMessage());
			}
		} else {
			// Just update without notification if unmarking as paid or already paid
			$visit->update(['paid' => $paid]);
		}

		return back()->with('success', __('Visit payment status updated.'));
	}
}



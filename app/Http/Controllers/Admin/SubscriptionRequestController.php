<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;

/**
 * Subscription requests submitted from the public pricing page: the queue of
 * clinics waiting to be set up. Read-mostly — an admin reviews each one, marks
 * how far it has been handled, and creates the real accounts via Clinic Quick
 * Setup using the (decrypted) credentials shown on the detail page.
 */
class SubscriptionRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionRequest::query();

        if (in_array($request->input('status'), ['new', 'contacted', 'activated'], true)) {
            $query->where('status', $request->input('status'));
        }

        if (in_array($request->input('plan'), ['starter', 'equipped', 'multi'], true)) {
            $query->where('plan', $request->input('plan'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_phone', 'like', "%{$search}%")
                    ->orWhere('doctor_name', 'like', "%{$search}%")
                    ->orWhere('clinic_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => SubscriptionRequest::count(),
            'new' => SubscriptionRequest::where('status', 'new')->count(),
            'contacted' => SubscriptionRequest::where('status', 'contacted')->count(),
            'activated' => SubscriptionRequest::where('status', 'activated')->count(),
        ];

        return view('admin.subscription-requests.index', compact('requests', 'stats'));
    }

    public function show(SubscriptionRequest $subscriptionRequest)
    {
        return view('admin.subscription-requests.show', [
            'req' => $subscriptionRequest,
        ]);
    }

    public function updateStatus(Request $request, SubscriptionRequest $subscriptionRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,activated',
        ]);

        $subscriptionRequest->update([
            'status' => $validated['status'],
            'reviewed_at' => now(),
        ]);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم تحديث حالة الطلب'
            : 'Request status updated');
    }

    public function destroy(SubscriptionRequest $subscriptionRequest)
    {
        $subscriptionRequest->delete();

        return redirect()->route('admin.subscription-requests.index')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم حذف الطلب'
                : 'Request deleted');
    }
}

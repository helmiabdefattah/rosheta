<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryOfferController extends Controller
{
    public function accepted(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Get accepted offers for this laboratory
        $query = Offer::where('laboratory_id', $laboratory->id)
            ->where('request_type', 'test')
            ->where('status', 'accepted')
            ->with([
                'request.client',
                'request.address.area.city.governorate',
                'user'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('request.client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $offers = $query->paginate(15);

        return view('laboratories.offers.accepted', compact('offers', 'laboratory'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Get all sent offers for this laboratory (all statuses)
        $query = Offer::where('laboratory_id', $laboratory->id)
            ->where('request_type', 'test')
            ->with([
                'request.client',
                'request.address.area.city.governorate',
                'user',
                'medicineLines' => function($q) {
                    $q->with('medicine:id,name');
                },
                'testLines' => function($q) {
                    $q->with('medicalTest:id,test_name_en,test_name_ar');
                }
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('request.client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $offers = $query->paginate(15);

        return view('laboratories.offers.index', compact('offers', 'laboratory'));
    }

    public function cancel(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Verify the offer belongs to this laboratory
        if ($offer->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.offers.index')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بإلغاء هذا العرض.' : 'You are not authorized to cancel this offer.');
        }

        // Only allow canceling pending offers
        if ($offer->status != 'pending') {
            return redirect()->route('laboratories.offers.index')
                ->with('error', app()->getLocale() === 'ar' ? 'يمكن إلغاء العروض المعلقة فقط.' : 'Only pending offers can be cancelled.');
        }

        // Update offer status to cancelled/rejected
        $offer->update(['status' => 'rejected']);

        return redirect()->route('laboratories.offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إلغاء العرض بنجاح' : 'Offer cancelled successfully');
    }
}


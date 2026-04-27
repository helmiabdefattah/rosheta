<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with(['request.client', 'pharmacy', 'laboratory'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhereHas('request.client', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('pharmacy', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('laboratory', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    });
            });
        }

        $offers = $query->paginate(15)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function show(Offer $offer)
    {
        $offer->load(['request.client', 'pharmacy', 'laboratory', 'lines']);
        return view('admin.offers.show', compact('offer'));
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('admin.offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف العرض بنجاح' : 'Offer deleted successfully');
    }

}


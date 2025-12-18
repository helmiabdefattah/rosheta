<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OfferController extends Controller
{
    public function index()
    {
        return view('admin.offers.index');
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

    public function data()
    {
        $offers = Offer::with(['request.client', 'pharmacy', 'laboratory'])->select('offers.*');

        return DataTables::of($offers)
            ->addColumn('request_id', function ($offer) {
                return $offer->request->id ?? '-';
            })
            ->addColumn('client_name', function ($offer) {
                return $offer->request->client->name ?? '-';
            })
            ->addColumn('provider_name', function ($offer) {
                return $offer->request_type == 'test' 
                    ? ($offer->laboratory->name ?? '-')
                    : ($offer->pharmacy->name ?? '-');
            })
            ->addColumn('status_badge', function ($offer) {
                $colors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'accepted' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                ];
                $color = $colors[$offer->status] ?? 'bg-gray-100 text-gray-800';
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . ucfirst($offer->status) . '</span>';
            })
            ->addColumn('total_price_formatted', function ($offer) {
                return $offer->total_price ? 'EGP ' . number_format($offer->total_price, 2) : '-';
            })
            ->addColumn('actions', function ($offer) {
                return view('admin.offers.actions', compact('offer'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }
}


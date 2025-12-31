<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientOfferController extends Controller
{
    public function index(Request $request)
    {
        $client = Auth::guard('client')->user();
        $filterType = $request->get('filter_type', 'all');

        // Start with base query
        $query = ClientRequest::where('client_id', $client->id)
            ->with([
                'offers' => function($query) use ($filterType) {
                    $query->whereIn('status', ['pending', 'accepted'])
                        ->with([
                            'pharmacy:id,name,phone',
                            'laboratory:id,name,phone',
                            'lines' => function($q) use ($filterType) {
                                $q->with([
                                    'medicine:id,name',
                                    'medicalTest:id,test_name_en,test_name_ar,type'
                                ]);

                                // Apply filter if not 'all'
                                if ($filterType !== 'all') {
                                    $q->where('item_type', $filterType);
                                }
                            }
                        ])
                        ->when($filterType !== 'all', function($q) use ($filterType) {
                            // Only get offers that have lines of the filtered type
                            $q->whereHas('lines', function($lineQuery) use ($filterType) {
                                $lineQuery->where('item_type', $filterType);
                            });
                        })
                        ->latest();
                }
            ])
            ->when($filterType !== 'all', function($q) use ($filterType) {
                // Only get requests of the specified type
                $q->where('type', $filterType);
            })
            ->latest();

        $requests = $query->get();

        // Filter offers to only include those with lines (after loading)
        $offersByRequest = $requests->map(function($request) use ($filterType) {
            // If filtering, only include offers that have lines of the filtered type
            if ($filterType !== 'all') {
                $filteredOffers = $request->offers->filter(function($offer) use ($filterType) {
                    return $offer->lines->contains('item_type', $filterType);
                });

                return [
                    'request' => $request,
                    'offers' => $filteredOffers,
                    'hasOffers' => $filteredOffers->count() > 0,
                ];
            }

            return [
                'request' => $request,
                'offers' => $request->offers,
                'hasOffers' => $request->offers->count() > 0,
            ];
        })->filter(fn($item) => $item['hasOffers']);

        // Determine default type for UI
        $defaultType = $filterType;

        // If it's an AJAX request, return only the partial
        if ($request->ajax()) {
            return view('client.offers.partials.offers-list', compact('offersByRequest', 'defaultType'))->render();
        }

        return view('client.offers.index', compact('offersByRequest', 'defaultType'));
    }
    public function getOffers(Request $request)
    {
        $client = Auth::guard('client')->user();

        // Get all requests for this client with their offers
        $requests = ClientRequest::where('client_id', $client->id)
            ->with([
                'offers' => function($query) {
                    $query->whereIn('status', ['pending', 'accepted'])
                        ->with([
                            'pharmacy:id,name,phone',
                            'laboratory:id,name,phone',
                            'lines' => function($q) {
                                $q->with(['medicine:id,name', 'medicalTest:id,test_name_en,test_name_ar']);
                            }
                        ])->latest();
                }
            ])
            ->latest()
            ->get();

        // Group offers by request
        $offersByRequest = $requests->map(function($request) {
            return [
                'request_id' => $request->id,
                'request_type' => $request->type,
                'request_status' => $request->status,
                'request_created_at' => $request->created_at->format('Y-m-d H:i:s'),
                'offers' => $request->offers->map(function($offer) {
                    return [
                        'id' => $offer->id,
                        'status' => $offer->status,
                        'vendor_status' => $offer->vendor_status,
                        'total_price' => $offer->total_price,
                        'provider_name' => $offer->request_type === 'test'
                            ? ($offer->laboratory->name ?? 'N/A')
                            : ($offer->pharmacy->name ?? 'N/A'),
                        'provider_phone' => $offer->request_type === 'test'
                            ? ($offer->laboratory->phone ?? null)
                            : ($offer->pharmacy->phone ?? null),
                        'lines' => $offer->lines->map(function($line) {
                            if ($line->item_type === 'test') {
                                return [
                                    'item_type' => 'test',
                                    'test_name_en' => $line->medicalTest->test_name_en ?? 'N/A',
                                    'test_name_ar' => $line->medicalTest->test_name_ar ?? null,
                                    'price' => $line->price ?? 0,
                                ];
                            } else {
                                return [
                                    'item_type' => 'medicine',
                                    'medicine_name' => $line->medicine->name ?? 'N/A',
                                    'quantity' => $line->quantity,
                                    'unit' => $line->unit,
                                    'price' => $line->price ?? 0,
                                ];
                            }
                        }),
                        'created_at' => $offer->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        })->filter(function($item) {
            return $item['offers']->count() > 0;
        });

        return response()->json([
            'success' => true,
            'data' => $offersByRequest->values(),
        ]);
    }

    public function accept(Offer $offer)
    {
        $client = Auth::guard('client')->user();

        // Ensure the offer belongs to a request owned by the client
        if ($offer->request->client_id !== $client->id) {
            abort(403);
        }

        // Ensure offer is pending
        if ($offer->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending offers can be accepted.']);
        }

        try {
            DB::transaction(function() use ($offer) {
                // Accept this offer
                $offer->update(['status' => 'accepted']);

                // Reject all other offers for the same request
                Offer::where('client_request_id', $offer->client_request_id)
                    ->where('id', '!=', $offer->id)
                    ->update(['status' => 'rejected']);

                // Update request status
                $offer->request->update(['status' => 'confirmed']);

                // Create order from accepted offer
                Order::create([
                    'client_request_id' => $offer->client_request_id,
                    'pharmacy_id' => $offer->pharmacy_id,
                    'laboratory_id' => $offer->laboratory_id,
                    'user_id' => $offer->user_id,
                    'offer_id' => $offer->id,
                    'status' => 'preparing',
                    'payment_method' => 'cash',
                    'payed' => false,
                    'total_price' => $offer->total_price,
                ]);
            });

            return redirect()->route('client.offers.index')
                ->with('success', app()->getLocale() === 'ar'
                    ? 'تم قبول العرض بنجاح'
                    : 'Offer accepted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to accept offer: ' . $e->getMessage()]);
        }
    }

    public function reject(Offer $offer)
    {
        $client = Auth::guard('client')->user();

        // Ensure the offer belongs to a request owned by the client
        if ($offer->request->client_id !== $client->id) {
            abort(403);
        }

        // Ensure offer is pending
        if ($offer->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending offers can be rejected.']);
        }

        $offer->update(['status' => 'rejected']);

        return redirect()->route('client.offers.index')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم رفض العرض'
                : 'Offer rejected');
    }
}


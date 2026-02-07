<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\Offer;
use App\Models\OfferLine;
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
            ->with(['insuranceCompany'])
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

                // Reload offer with relationships for notification
                $offer->refresh();
                $offer->load(['request.client', 'pharmacy', 'laboratory', 'user']);

                // Notify the offer creator (pharmacy/lab owner) about acceptance
                if ($offer->user) {
                    try {
                        $offer->user->notify(new \App\Notifications\OfferAcceptedNotification($offer));
                        \Log::info('OfferAcceptedNotification dispatched', [
                            'offer_id' => $offer->id,
                            'user_id' => $offer->user->id,
                            'user_fcm_web' => !empty($offer->user->fcm_token_web),
                            'user_fcm_mobile' => !empty($offer->user->fcm_token_mobile),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to dispatch OfferAcceptedNotification', [
                            'offer_id' => $offer->id,
                            'user_id' => $offer->user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
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

    /**
     * Create a direct offer (accept medical test offer from laboratory)
     */
    public function createDirectOffer(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'type' => 'required|in:medicine,test',
            'note' => 'nullable|string',
            'pharmacy_id' => 'required_if:type,medicine|nullable|exists:pharmacies,id',
            'laboratory_id' => 'required_if:type,test|nullable|exists:laboratories,id',
            'client_address_id' => 'nullable|exists:client_addresses,id',
            'lines' => 'required|array|min:1',
            'lines.*.item_type' => 'required|in:medicine,test',
            'lines.*.medicine_id' => 'required_if:lines.*.item_type,medicine|nullable|exists:medicines,id',
            'lines.*.medical_test_id' => 'required_if:lines.*.item_type,test|nullable|exists:medical_tests,id',
            'lines.*.quantity' => 'nullable|integer|min:1',
            'lines.*.unit' => 'nullable|string',
            'lines.*.price' => 'required|numeric|min:0',
        ]);

        if (!empty($validated['client_address_id'])) {
            \App\Models\ClientAddress::where('id', $validated['client_address_id'])
                ->where('client_id', $client->id)
                ->firstOrFail();
        }

        DB::beginTransaction();

        try {
            $clientRequest = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => 'confirmed',
                'type' => $validated['type'],
            ]);

            foreach ($validated['lines'] as $line) {
                ClientRequestLine::create([
                    'client_request_id' => $clientRequest->id,
                    'item_type' => $line['item_type'],
                    'medicine_id' => $line['medicine_id'] ?? null,
                    'medical_test_id' => $line['medical_test_id'] ?? null,
                    'quantity' => $line['quantity'] ?? 1,
                    'unit' => $line['unit'] ?? null,
                ]);
            }

            $totalPrice = collect($validated['lines'])->sum(
                fn ($line) => ($line['quantity'] ?? 1) * $line['price']
            );

            $offer = Offer::create([
                'client_request_id' => $clientRequest->id,
                'pharmacy_id' => $validated['type'] === 'medicine' ? $validated['pharmacy_id'] : null,
                'laboratory_id' => $validated['type'] === 'test' ? $validated['laboratory_id'] : null,
                'user_id' => $client->id,
                'status' => 'accepted',
                'vendor_status' => 'preparing',
                'total_price' => $totalPrice,
                'request_type' => $validated['type'],
            ]);

            foreach ($validated['lines'] as $line) {
                OfferLine::create([
                    'offer_id' => $offer->id,
                    'item_type' => $line['item_type'],
                    'medicine_id' => $line['medicine_id'] ?? null,
                    'medical_test_id' => $line['medical_test_id'] ?? null,
                    'quantity' => $line['quantity'] ?? 1,
                    'unit' => $line['unit'] ?? null,
                    'price' => $line['price'],
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => __('Offer accepted successfully'),
                    'offer' => $offer->load([
                        'request.lines',
                        'medicineLines.medicine',
                        'testLines.medicalTest',
                        'pharmacy',
                        'laboratory',
                    ]),
                ]);
            }

            // ✅ THIS WILL NOW RUN
            return redirect()
                ->route('client.test-results.index')
                ->with('success', __('Offer accepted successfully'));

        } catch (\Throwable $e) {
            report($e);
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}


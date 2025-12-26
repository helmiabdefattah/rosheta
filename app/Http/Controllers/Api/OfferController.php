<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\MedicalTestOffer;
use App\Models\Offer;
use App\Models\OfferLine;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Laboratory;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    /**
     * Get all offers for a specific request if the client owns the request
     */
    public function offersList(Request $request)
    {
        $request->validate([
            'request_id' => 'required|integer',
            'client_id' => 'required|integer',
        ]);

        $requestId = $request->request_id;
        $clientId = $request->client_id;

        // 1️⃣ Check that the request belongs to the client
        $clientRequest = ClientRequest::where('id', $requestId)
            ->where('client_id', $clientId)
            ->first();
        if (!$clientRequest) {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: This request does not belong to the client.',
            ], 403);
        }
        // 2️⃣ Load offers with relations
        $offers = Offer::where('client_request_id', $requestId)
            ->select('id', 'client_request_id', 'pharmacy_id', 'status', 'total_price','laboratory_id')
            ->with([
                'pharmacy:id,name',
                'lines:id,offer_id,medicine_id,quantity,unit,price',
                'lines.medicine:id,name,arabic',
                'laboratory:id,name,phone',
                'testLines:id,offer_id,medical_test_id',
                'testLines.medicalTest:id,test_name_en,test_name_ar',
            ])
            ->get();
//dd($offers);
        return response()->json([
            'status' => true,
            'offers' => $offers,
        ]);
    }
    public function confirmedTestOffersList(Request $request)
    {
        $request->validate([
            'client_id'  => 'required|integer',
            'request_id' => 'nullable|integer',
        ]);

        $clientId  = $request->client_id;
        $requestId = $request->request_id;


        if ($requestId) {
            $clientRequest = ClientRequest::where('id', $requestId)
                ->where('client_id', $clientId)
                ->first();

            if (!$clientRequest) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized: This request does not belong to the client.',
                ], 403);
            }
        }


        $offersQuery = Offer::where('status', 'accepted')->where('request_type','test')
            ->whereHas('request', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });


        if ($requestId) {
            $offersQuery->where('client_request_id', $requestId);
        }

        $offers = $offersQuery
            ->select(
                'id',
                'client_request_id',
                'vendor_status',
                'status',
                'total_price',
                'laboratory_id'
            )
            ->with([
                'attachments',
                'laboratory:id,name',
                'testLines:id,offer_id,medical_test_id',
                'testLines.medicalTest:id,test_name_en,test_name_ar',
            ])
            ->get();

        return response()->json([
            'status' => true,
            'offers' => $offers,
        ]);
    }

    public function medicalTestOffersList()
    {
        $offers = MedicalTestOffer::with(['laboratory', 'medicalTest'])
            ->get()
            ->map(function ($offer) {

                // If offer_price is null calculate the discounted price
                $offerPrice = $offer->offer_price
                    ?? ($offer->price - ($offer->price * ($offer->discount / 100)));

                return [
                    'medical_test_id' => $offer->medical_test_id,
                    'laboratory_id' => $offer->laboratory->id,
                    'laboratory_name'    => $offer->laboratory->name,
                    'laboratory_phone' => $offer->laboratory->phone,
                    'laboratory_lat' => $offer->laboratory->lat,
                    'laboratory_lng' => $offer->laboratory->lng,
                    'laboratory_address' => $offer->laboratory->address,
                    'laboratory_opening_time' => $offer->laboratory->opening_time,
                    'laboratory_closing_time' => $offer->laboratory->closing_time,
                    'laboratory_logo'    => $offer->laboratory?->logo
                        ? asset('storage/' . $offer->laboratory->logo)
                        : null,
                    'test_name_en'       => $offer->medicalTest->test_name_en,
                    'test_name_ar'       => $offer->medicalTest->test_name_ar,
                    'medical_test_id'       => $offer->medicalTest->id,

                    // Cast to int
                    'price'              => (int) round($offer->price),
                    'offer_price'        => (int) round($offerPrice),
                    'discount'           => (int) round($offer->discount),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $offers
        ]);
    }

    public function labList(Request $request): JsonResponse
    {
        // Check if lab_name parameter is provided
        $labName = $request->query('lab_name');

        if ($labName) {
            // Find specific lab by name (exact match or similar)
            $lab = Laboratory::with(['area', 'user'])
                ->where('is_active', true)
                ->where('name', 'LIKE', '%' . $labName . '%')
                ->first();

            if (!$lab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laboratory not found'
                ], 404);
            }

            // Return single lab data with offers if available
            $labData = [
                'id' => $lab->id,
                'name' => $lab->name,
                'phone' => $lab->phone,
                'email' => $lab->email,
                'address' => $lab->address,
                'lat' => $lab->lat,
                'lng' => $lab->lng,
                'opening_time' => $lab->opening_time,
                'closing_time' => $lab->closing_time,
                'area' => $lab->area?->name,
                'logo' => $lab->getFirstMediaUrl('logo'),
                'logo_thumb' => $lab->getFirstMediaUrl('logo', 'thumb'),
            ];

            // If you have offers relationship, add it like this:
            // $labData['offers'] = $lab->offers->map(function($offer) { ... });

            return response()->json([
                'success' => true,
                'data' => $labData
            ]);
        }

        // Original logic for all labs
        $labs = Laboratory::with(['area', 'user'])
            ->where('is_active', true)
            ->get()
            ->map(function ($lab) {
                return [
                    'id' => $lab->id,
                    'name' => $lab->name,
                    'phone' => $lab->phone,
                    'email' => $lab->email,
                    'address' => $lab->address,
                    'lat' => $lab->lat,
                    'lng' => $lab->lng,
                    'opening_time' => $lab->opening_time,
                    'closing_time' => $lab->closing_time,
                    'area' => $lab->area?->name,
                    'logo' => $lab->getFirstMediaUrl('logo'),
                    'logo_thumb' => $lab->getFirstMediaUrl('logo', 'thumb'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $labs,
        ]);
    }
    public function clientOrders(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'status' => 'sometimes|string|in:pending,preparing,delivering,delivered', // Order status filter
        ]);

        $clientId = $request->client_id;
        $status = $request->status;

        // Get orders for this client
        $orders = Order::whereHas('request', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
            ->with([
                'request:id,created_at',
                'pharmacy:id,name,address,phone',
                'offer:id,total_price',
                'lines:id,order_id,medicine_id,quantity,unit,price',
                'lines.medicine:id,name,arabic',
            ]);

        // Filter by order status if provided
        if ($status) {
            $orders->where('status', $status);
        }

        // Order by latest orders first
        $orders->orderBy('created_at', 'desc');

        $orders = $orders->get();

        return response()->json([
            'status' => true,
            'message' => 'Client orders retrieved successfully',
            'orders' => $orders,
            'count' => $orders->count(),
        ]);
    }
    public function createDirectOffer(Request $request)
    {
        $request->validate([
            'type' => 'required|in:medicine,test',

            // request data
            'note' => 'nullable|string',

            // provider
            'pharmacy_id' => 'required_if:type,medicine|nullable|exists:pharmacies,id',
            'laboratory_id' => 'required_if:type,test|nullable|exists:laboratories,id',

            // lines
            'lines' => 'required|array|min:1',
            'lines.*.item_type' => 'required|in:medicine,test',
            'lines.*.medicine_id' => 'required_if:lines.*.item_type,medicine|nullable|exists:medicines,id',
            'lines.*.medical_test_id' => 'required_if:lines.*.item_type,test|nullable|exists:medical_tests,id',
            'lines.*.quantity' => 'nullable|integer|min:1',
            'lines.*.unit' => 'nullable|string',
            'lines.*.price' => 'required|numeric|min:0',
        ]);

        \DB::beginTransaction();

        try {
            /** 1️⃣ Create Client Request */
            $clientRequest = ClientRequest::create([
                'client_id' => auth()->id(),
                'client_address_id' => $request->client_address_id,
                'note' => $request->note,
                'status' => 'confirmed',
                'type' => $request->type,
            ]);

            /** 2️⃣ Create Client Request Lines */
            foreach ($request->lines as $line) {
                ClientRequestLine::create([
                    'client_request_id' => $clientRequest->id,
                    'item_type' => $line['item_type'],
                    'medicine_id' => $line['medicine_id'] ?? null,
                    'medical_test_id' => $line['medical_test_id'] ?? null,
                    'quantity' => $line['quantity'] ?? 1,
                    'unit' => $line['unit'] ?? null,
                ]);
            }

            /** 3️⃣ Calculate total price */
            $totalPrice = collect($request->lines)->sum(function ($line) {
                return ($line['quantity'] ?? 1) * $line['price'];
            });

            /** 4️⃣ Create Accepted Offer */
            $offer = Offer::create([
                'client_request_id' => $clientRequest->id,
                'pharmacy_id' => $request->type === 'medicine' ? $request->pharmacy_id : null,
                'laboratory_id' => $request->type === 'test' ? $request->laboratory_id : null,
                'user_id' => auth()->id(),
                'status' => 'accepted',
                'vendor_status' => 'preparing',
                'total_price' => $totalPrice,
                'request_type' => $request->type,
            ]);

            /** 5️⃣ Create Offer Lines */
            foreach ($request->lines as $line) {
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

            \DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Accepted offer created successfully',
                'offer' => $offer->load([
                    'request.lines',
                    'medicineLines.medicine',
                    'testLines.medicalTest',
                    'pharmacy',
                    'laboratory',
                ]),
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}

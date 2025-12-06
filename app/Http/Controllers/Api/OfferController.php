<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\MedicalTestOffer;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;

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
            ->select('id', 'client_request_id', 'pharmacy_id', 'status', 'total_price')
            ->with([
                'pharmacy:id,name',
                'lines:id,offer_id,medicine_id,quantity,unit,price',
                'lines.medicine:id,name,arabic',
            ])
            ->get();
//dd($offers);
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
                    'laboratory_name'    => $offer->laboratory->name,
                    'laboratory_address' => $offer->laboratory->address,
                    'laboratory_logo'    => $offer->laboratory?->logo
                        ? asset('storage/' . $offer->laboratory->logo)
                        : null,
                    'test_name_en'       => $offer->medicalTest->test_name_en,
                    'test_name_ar'       => $offer->medicalTest->test_name_ar,

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
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\Offer;
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
}

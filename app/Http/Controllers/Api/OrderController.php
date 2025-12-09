<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function handleOffer(Request $request)
    {
        $request->validate([
            'offer_id' => 'required|exists:offers,id',
            'action' => 'required|in:accepted',
            'payment_method' => 'nullable|string',
        ]);

        $offer = Offer::with(['lines', 'request'])->findOrFail($request->offer_id);

        // Security: user must own the request
        if ($offer->request->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 1️⃣ Accept this offer
        $offer->update(['status' => 'accepted']);

        // 2️⃣ Reject all other offers for the same request
        Offer::where('client_request_id', $offer->client_request_id)
            ->where('id', '!=', $offer->id)
            ->update(['status' => 'rejected']);

        // 3️⃣ Create order from accepted offer
        $order = Order::create([
            'client_request_id' => $offer->client_request_id,
            'pharmacy_id' => $offer->pharmacy_id,
            'user_id' => auth()->id(),
            'offer_id' => $offer->id,
            'status' => 'preparing',
            'payment_method' => $request->payment_method ?? 'cash',
            'payed' => false,
            'total_price' => $offer->total_price,
        ]);

        // 4️⃣ Create order lines from offer lines
        foreach ($offer->lines as $line) {

            OrderLine::create([
                'order_id' => $order->id,
                'medicine_id' => $line->medicine_id,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'price' => $line->price,
            ]);
        }
//        dd($order);

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load('lines.medicine'),
        ]);
    }
    public function trackOrder($orderId)
    {
        $order = Order::with([
            'pharmacy:id,name',
            'offer:id,total_price',
            'lines.medicine:id,name,img',
        ])
            ->where('id', $orderId)
            ->where('user_id', auth()->id())   // security
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found or unauthorized.'
            ], 404);
        }

        return response()->json([
            'order_id' => $order->id,
            'status'   => $order->status,
            'payment_method' => $order->payment_method,
            'payed' => $order->payed,
            'total_price' => $order->total_price,

            // Pharmacy Info
            'pharmacy' => [
                'name' => $order->pharmacy->name ?? null,

            ],

            // Order Lines
            'items' => $order->lines->map(function ($line) {
                return [
                    'medicine_id' => $line->medicine_id,
                    'medicine_name' => $line->medicine->name ?? null,
                    'medicine_img' => $line->medicine->img ?? null,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'price' => $line->price,
                ];
            }),
        ]);
    }

}

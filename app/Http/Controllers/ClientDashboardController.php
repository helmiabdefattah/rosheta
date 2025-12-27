<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();

        // Statistics
        $stats = [
            'total_requests' => ClientRequest::where('client_id', $client->id)->count(),
            'pending_requests' => ClientRequest::where('client_id', $client->id)
                ->where('status', 'pending')
                ->count(),
            'total_orders' => Order::whereHas('request', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->count(),
            'active_orders' => Order::whereHas('request', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
        ];

        // Recent requests
        $recentRequests = ClientRequest::where('client_id', $client->id)
            ->latest()
            ->limit(5)
            ->get();

        // Recent orders
        $recentOrders = Order::whereHas('request', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
        ->with(['request', 'pharmacy'])
        ->latest()
        ->limit(5)
        ->get();

        return view('client.dashboard', compact('stats', 'recentRequests', 'recentOrders'));
    }
}


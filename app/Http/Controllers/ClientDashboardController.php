<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\NurseVisit;
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
            'scheduled_visits' => NurseVisit::whereHas('request', fn($q) => $q->where('client_id', $client->id))
                ->where('status', 'scheduled')
                ->count(),
            'test_results' => \App\Models\Offer::where('status', 'accepted')
                ->whereIn('request_type', ['test', 'radiology'])
                ->whereHas('request', function($q) use ($client) {
                    $q->where('client_id', $client->id);
                })
                ->whereHas('attachments')
                ->count(),
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

        // Client visits with reviews
        $visits = NurseVisit::with(['request.client', 'review', 'offer'])
            ->whereHas('request', fn($q) => $q->where('client_id', $client->id))
            ->orderByDesc('visit_datetime')
            ->paginate(10); // pagination optional

        // Available bonus points (not used and active)
        $availableBonusPoints = \App\Models\BonusPoint::where('client_id', $client->id)
            ->where('used', false)
            ->where('status', 'active')
            ->sum('points');

        return view('client.dashboard', compact('stats', 'recentRequests', 'recentOrders', 'visits', 'availableBonusPoints'));
    }
}


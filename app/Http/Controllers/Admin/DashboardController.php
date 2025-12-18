<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientRequest;
use App\Models\Governorate;
use App\Models\Laboratory;
use App\Models\MedicalTest;
use App\Models\Medicine;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'medicines' => Medicine::count(),
            'users' => User::count(),
            'pharmacies' => Pharmacy::count(),
            'laboratories' => Laboratory::count(),
            'clients' => Client::count(),
            'client_requests' => ClientRequest::count(),
            'orders' => Order::count(),
            'offers' => Offer::count(),
            'medical_tests' => MedicalTest::count(),
            'governorates' => Governorate::count(),
            'cities' => City::count(),
            'areas' => Area::count(),
        ];

        // Recent activity
        $recentRequests = ClientRequest::with('client')
            ->latest()
            ->limit(5)
            ->get();

        $recentOrders = Order::with(['request.client', 'pharmacy'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'recentOrders'));
    }
}


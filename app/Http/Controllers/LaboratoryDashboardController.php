<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Laboratory;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get the laboratory for the authenticated user
        $laboratory = null;
        if ($user->laboratory_id) {
            $laboratory = Laboratory::with('user')->find($user->laboratory_id);
        }

        // If user doesn't have a laboratory, redirect or show error
        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Statistics
        $stats = [
            'total_requests' => ClientRequest::where('type', 'test')->count(),
            'pending_requests' => ClientRequest::where('type', 'test')
                ->where('status', 'pending')
                ->count(),
            'total_offers' => Offer::where('laboratory_id', $laboratory->id)
                ->where('request_type', 'test')
                ->count(),
            'accepted_offers' => Offer::where('laboratory_id', $laboratory->id)
                ->where('request_type', 'test')
                ->where('status', 'accepted')
                ->count(),
            'pending_offers' => Offer::where('laboratory_id', $laboratory->id)
                ->where('request_type', 'test')
                ->where('status', 'pending')
                ->count(),
            'total_users' => User::where('laboratory_id', $laboratory->id)->count(),
        ];

        // Get recent pending test requests (type = 'test' and status = 'pending') that don't have offers from this laboratory
        $recentRequests = ClientRequest::where('type', 'test')
            ->where('status', 'pending')
            ->whereDoesntHave('offers', function($q) use ($laboratory) {
                $q->where('laboratory_id', $laboratory->id);
            })
            ->with([
                'client',
                'address.area.city.governorate',
                'lines' => function($q) {
                    $q->where('item_type', 'test')->with('medicalTest');
                },
                'offers' => function($q) use ($laboratory) {
                    $q->where('laboratory_id', $laboratory->id);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('laboratories.dashboard', compact('laboratory', 'stats', 'recentRequests'));
    }
}

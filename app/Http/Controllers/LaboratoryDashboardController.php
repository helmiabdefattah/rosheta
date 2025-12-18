<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get the laboratory for the authenticated user
        $laboratory = null;
        if ($user->laboratory_id) {
            $laboratory = Laboratory::find($user->laboratory_id);
        }

        // If user doesn't have a laboratory, redirect or show error
        if (!$laboratory) {
            return redirect()->route('filament.admin.resources.laboratories.index')
                ->with('error', 'You are not associated with any laboratory.');
        }

        // Get pending test requests (type = 'test' and status = 'pending')
        $query = ClientRequest::where('type', 'test')
            ->where('status', 'pending')
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
            ->orderBy('created_at', 'desc');

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->paginate(15);

        return view('laboratories.dashboard', compact('requests', 'laboratory'));
    }
}

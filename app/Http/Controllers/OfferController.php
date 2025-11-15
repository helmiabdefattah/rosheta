<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Offer;
use App\Models\OfferLine;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function create(Request $request)
    {
        $clientRequestId = $request->query('request');
        $clientRequest = $clientRequestId ? ClientRequest::with(['client', 'lines.medicine', 'address'])->find($clientRequestId) : null;
        
        $clientRequests = ClientRequest::with('client')->get()->mapWithKeys(function ($r) {
            return [$r->id => "Request #{$r->id} - {$r->client->name}"];
        });
        
        $pharmacies = Pharmacy::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        
        return view('offers.create', compact('clientRequests', 'pharmacies', 'users', 'clientRequest'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_request_id' => 'required|exists:client_requests,id',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,submitted,accepted,cancelled',
            'offer_lines' => 'required|array|min:1',
            'offer_lines.*.medicine_id' => 'required|exists:medicines,id',
            'offer_lines.*.quantity' => 'required|integer|min:1',
            'offer_lines.*.unit' => 'required|in:box,strips,bottle,pack,piece',
            'offer_lines.*.price' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            // Calculate total price
            $totalPrice = collect($validated['offer_lines'])->sum(function ($line) {
                return $line['quantity'] * $line['price'];
            });
            
            // Create offer
            $offer = Offer::create([
                'client_request_id' => $validated['client_request_id'],
                'pharmacy_id' => $validated['pharmacy_id'],
                'user_id' => $validated['user_id'],
                'status' => $validated['status'],
                'total_price' => $totalPrice,
            ]);
            
            // Create offer lines
            foreach ($validated['offer_lines'] as $line) {
                OfferLine::create([
                    'offer_id' => $offer->id,
                    'medicine_id' => $line['medicine_id'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'price' => $line['price'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('offers.show', $offer->id)
                ->with('success', 'Offer created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create offer: ' . $e->getMessage());
        }
    }
    
    public function show(Offer $offer)
    {
        $offer->load(['request.client', 'request.lines.medicine', 'pharmacy', 'user', 'lines.medicine']);
        return view('offers.show', compact('offer'));
    }
}

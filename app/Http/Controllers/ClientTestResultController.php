<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientTestResultController extends Controller
{
    /**
     * Display a list of medical test results for the client.
     */
    public function index(Request $request)
    {
        $client = Auth::guard('client')->user();

        // Get accepted offers for medical tests or radiology that belong to this client
        $results = Offer::where('status', 'accepted')
            ->whereIn('request_type', ['test', 'radiology'])
            ->whereHas('request', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->with([
                'request',
                'laboratory',
                'attachments',
                'testLines' => function($q) {
                    $q->with('medicalTest');
                }
            ])
            ->latest()
            ->paginate(10);

        return view('client.test-results.index', compact('results'));
    }

    /**
     * Show details of a specific test result.
     */
    public function show(Offer $offer)
    {
        $client = Auth::guard('client')->user();

        // Ensure the offer belongs to the client and is a test offer
        if ($offer->request->client_id !== $client->id || !in_array($offer->request_type, ['test', 'radiology'])) {
            abort(403);
        }

        $offer->load([
            'request',
            'laboratory',
            'attachments',
            'testLines.medicalTest'
        ]);

        return view('client.test-results.show', compact('offer'));
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Models\Offer;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-offer-lines/{client_request_id}', function ($client_request_id) {
    // Find the first offer with this client_request_id
    $offer = Offer::where('client_request_id', $client_request_id)->first();

    if (!$offer) {
        return "No offer found for client_request_id {$client_request_id}";
    }

    // Dump the request lines
    dd($offer->requestLines->toArray());
});

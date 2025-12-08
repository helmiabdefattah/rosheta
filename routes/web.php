<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\LocaleController;
use App\Models\Offer;
use App\Models\ClientRequest;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale');

// Offer routes
Route::get('/admin/offers/create/{request}', [OfferController::class, 'create'])->name('offers.create');
Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');

// API routes for AJAX
Route::get('/api/client-requests/{id}/images', function ($id) {
    $request = ClientRequest::find($id);
    return response()->json(['images' => $request?->images ?? []]);
});

Route::get('/api/client-requests/{id}/lines', function ($id) {
    $request = ClientRequest::with('lines.medicine')->find($id);
    if (!$request) {
        return response()->json(['lines' => []]);
    }

    $lines = $request->lines->map(function ($line) {
        return [
            'medicine_id' => $line->medicine_id,
            'medicine_name' => $line->medicine->name ?? 'N/A',
            'quantity' => $line->quantity,
            'unit' => $line->unit,
        ];
    })->toArray();

    return response()->json(['lines' => $lines]);
});



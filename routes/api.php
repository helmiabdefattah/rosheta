<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientRequestController;

Route::post('/client-requests', [ClientRequestController::class, 'store']);

Route::get('/medicines/search', function (Request $request) {
    $search = $request->get('search');

    $medicines = \App\Models\Medicine::when($search, function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
            ->orWhere('generic_name', 'like', "%{$search}%");
    })
        ->select('id', 'name', 'dosage_form')
        ->orderBy('name')
        ->paginate(25);

    return response()->json([
        'data' => $medicines->map(function ($medicine) {
            return [
                'id' => $medicine->id,
                'text' => $medicine->name,
                'dosage_form' => $medicine->dosage_form
            ];
        }),
        'next_page_url' => $medicines->nextPageUrl()
    ]);
})->name('medicines.search');


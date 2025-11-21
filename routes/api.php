<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientRequestController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/client-requests', [ClientRequestController::class, 'index']);
    Route::post('/client-requests', [ClientRequestController::class, 'store']);
});


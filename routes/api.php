<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ClientAddressController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\AuthResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientRequestController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/send-code', [AuthResetController::class, 'sendCode']);
Route::post('/password/check-code', [AuthResetController::class, 'checkCode']);
Route::post('/password/reset', [AuthResetController::class, 'resetPassword']);
// Public endpoints for areas and cities
Route::get('/areas', [AreaController::class, 'index']);
Route::get('/cities', [CityController::class, 'index']);
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
    Route::get('/verify', [AuthController::class, 'verify']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/client-requests', [ClientRequestController::class, 'index']);
    Route::post('/client-requests', [ClientRequestController::class, 'store']);
    Route::get('/offers/list', [OfferController::class, 'offersList']);
    Route::get('/offers/confirmed', [OfferController::class, 'confirmedTestOffersList']);
    Route::post('/offers/action', [OrderController::class, 'handleOffer']);
    Route::post('/offers/direct', [OfferController::class, 'createDirectOffer']);
    Route::get('/orders/{orderId}/track', [OrderController::class, 'trackOrder']);
    Route::get('/client-orders', [OfferController::class, 'clientOrders']);

    Route::get('/medicines', [ClientRequestController::class, 'medicineList']);
    Route::get('/medical-tests', [ClientRequestController::class, 'testsList']);
    Route::post('/medical-test-requests', [ClientRequestController::class, 'test_requests']);
    Route::get('/medical-test-offers', [OfferController::class, 'medicalTestOffersList']);
    Route::get('/labs', [OfferController::class, 'labList']);

    Route::get('/client/addresses', [ClientAddressController::class, 'index']);
    Route::post('/client-addresses', [ClientAddressController::class, 'store']);
    Route::put('/client-addresses/{id}', [ClientAddressController::class, 'update']);
    Route::delete('/client-addresses/{id}', [ClientAddressController::class, 'destroy']);
});


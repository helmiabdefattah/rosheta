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

// Auth routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/admin/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Registration routes
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Client Dashboard
Route::middleware('auth:client')->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClientDashboardController::class, 'index'])->name('dashboard');

    // Test Requests
    Route::get('/test-requests/create/{type}', [App\Http\Controllers\ClientTestRequestController::class, 'create'])->name('test-requests.create');
    Route::post('/test-requests/{type}', [App\Http\Controllers\ClientTestRequestController::class, 'store'])->name('test-requests.store');

    // Offers
    Route::get('/offers', [App\Http\Controllers\ClientOfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/get', [App\Http\Controllers\ClientOfferController::class, 'getOffers'])->name('offers.get');
    Route::put('/offers/{offer}/accept', [App\Http\Controllers\ClientOfferController::class, 'accept'])->name('offers.accept');
    Route::put('/offers/{offer}/reject', [App\Http\Controllers\ClientOfferController::class, 'reject'])->name('offers.reject');

    // Addresses
    Route::get('/addresses', [App\Http\Controllers\ClientAddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [App\Http\Controllers\ClientAddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [App\Http\Controllers\ClientAddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [App\Http\Controllers\ClientAddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [App\Http\Controllers\ClientAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\ClientAddressController::class, 'destroy'])->name('addresses.destroy');

    // Profile
    Route::get('/profile/edit', [App\Http\Controllers\ClientProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ClientProfileController::class, 'update'])->name('profile.update');
});

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

// Laboratory routes
Route::get('/admin/laboratories/create', [App\Http\Controllers\LaboratoryController::class, 'create'])->name('laboratories.create');
Route::get('/admin/laboratories/{laboratory}/edit', [App\Http\Controllers\LaboratoryController::class, 'edit'])->name('laboratories.edit');
Route::put('/admin/laboratories/{laboratory}', [App\Http\Controllers\LaboratoryController::class, 'update'])->name('laboratories.update');

// Laboratory Dashboard (for laboratory owners)
// Laboratory Dashboard Routes
Route::middleware('auth')->prefix('laboratory')->name('laboratories.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\LaboratoryDashboardController::class, 'index'])->name('dashboard');

    // Requests
    Route::get('/requests', [App\Http\Controllers\LaboratoryRequestController::class, 'index'])->name('requests.index');

    // Offers
    Route::get('/offers', [App\Http\Controllers\LaboratoryOfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/accepted', [App\Http\Controllers\LaboratoryOfferController::class, 'accepted'])->name('offers.accepted');
    Route::put('/offers/{offer}/cancel', [App\Http\Controllers\LaboratoryOfferController::class, 'cancel'])->name('offers.cancel');
    Route::put('/offers/{offer}/vendor-status', [App\Http\Controllers\LaboratoryOfferController::class, 'updateVendorStatus'])->name('offers.update-vendor-status');
    Route::post('/offers/{offer}/attachments', [App\Http\Controllers\LaboratoryOfferController::class, 'uploadAttachment'])->name('offers.upload-attachment');
    Route::delete('/offers/{offer}/attachments/{attachment}', [App\Http\Controllers\LaboratoryOfferController::class, 'deleteAttachment'])->name('offers.delete-attachment');

    // Profile
    Route::get('/profile/edit', [App\Http\Controllers\LaboratoryProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{laboratory}', [App\Http\Controllers\LaboratoryProfileController::class, 'update'])->name('profile.update');

    // Users
    Route::get('/users/data', [App\Http\Controllers\LaboratoryUserController::class, 'data'])->name('users.data');
    Route::resource('users', App\Http\Controllers\LaboratoryUserController::class);

    // Test Prices
    Route::get('/test-prices', [App\Http\Controllers\LaboratoryTestPriceController::class, 'index'])->name('test-prices.index');
    Route::post('/test-prices/store-or-update', [App\Http\Controllers\LaboratoryTestPriceController::class, 'storeOrUpdate'])->name('test-prices.store-or-update');
});

// Admin routes (Blade-based admin panel)
Route::middleware(['auth', \App\Http\Middleware\RedirectLaboratoryOwner::class])->prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin to /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Areas
    Route::get('/areas/data', [App\Http\Controllers\Admin\AreaController::class, 'data'])->name('areas.data');
    Route::resource('areas', App\Http\Controllers\Admin\AreaController::class);

    // Cities
    Route::get('/cities/data', [App\Http\Controllers\Admin\CityController::class, 'data'])->name('cities.data');
    Route::resource('cities', App\Http\Controllers\Admin\CityController::class);

    // Governorates
    Route::get('/governorates/data', [App\Http\Controllers\Admin\GovernorateController::class, 'data'])->name('governorates.data');
    Route::resource('governorates', App\Http\Controllers\Admin\GovernorateController::class);

    // Medical Tests
    Route::get('/medical-tests/data', [App\Http\Controllers\Admin\MedicalTestController::class, 'data'])->name('medical-tests.data');
    Route::resource('medical-tests', App\Http\Controllers\Admin\MedicalTestController::class);

    // Medicines
    Route::get('/medicines/data', [App\Http\Controllers\Admin\MedicineController::class, 'data'])->name('medicines.data');
    Route::resource('medicines', App\Http\Controllers\Admin\MedicineController::class);

    // Users
    Route::get('/users/data', [App\Http\Controllers\Admin\UserController::class, 'data'])->name('users.data');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);

    // Pharmacies
    Route::get('/pharmacies/data', [App\Http\Controllers\Admin\PharmacyController::class, 'data'])->name('pharmacies.data');
    Route::resource('pharmacies', App\Http\Controllers\Admin\PharmacyController::class);

    // Laboratories
    Route::get('/laboratories/data', [App\Http\Controllers\Admin\LaboratoryController::class, 'data'])->name('laboratories.data');
    Route::get('/laboratories', [App\Http\Controllers\Admin\LaboratoryController::class, 'index'])->name('laboratories.index');
    Route::get('/laboratories/create', [App\Http\Controllers\Admin\LaboratoryController::class, 'create'])->name('laboratories.create');
    Route::post('/laboratories', [App\Http\Controllers\Admin\LaboratoryController::class, 'store'])->name('laboratories.store');
    Route::get('/laboratories/{laboratory}/edit', [App\Http\Controllers\Admin\LaboratoryController::class, 'edit'])->name('laboratories.edit');
    Route::put('/laboratories/{laboratory}', [App\Http\Controllers\Admin\LaboratoryController::class, 'update'])->name('laboratories.update');
    Route::delete('/laboratories/{laboratory}', [App\Http\Controllers\Admin\LaboratoryController::class, 'destroy'])->name('laboratories.destroy');

    // Clients
    Route::get('/clients/data', [App\Http\Controllers\Admin\ClientController::class, 'data'])->name('clients.data');
    Route::resource('clients', App\Http\Controllers\Admin\ClientController::class);

    // Client Requests
    Route::get('/client-requests/data', [App\Http\Controllers\Admin\ClientRequestController::class, 'data'])->name('client-requests.data');
    Route::resource('client-requests', App\Http\Controllers\Admin\ClientRequestController::class);

    // Orders
    Route::get('/orders/data', [App\Http\Controllers\Admin\OrderController::class, 'data'])->name('orders.data');
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);

    // Offers
    Route::get('/offers/data', [App\Http\Controllers\Admin\OfferController::class, 'data'])->name('offers.data');
    Route::resource('offers', App\Http\Controllers\Admin\OfferController::class);

    // Medical Test Offers
    Route::get('/medical-test-offers/data', [App\Http\Controllers\Admin\MedicalTestOfferController::class, 'data'])->name('medical-test-offers.data');
    Route::resource('medical-test-offers', App\Http\Controllers\Admin\MedicalTestOfferController::class);
});



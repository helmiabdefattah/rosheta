
<?php

use App\Http\Controllers\NurseOfferController;
use App\Http\Controllers\WebviewBridgeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\LocaleController;
use App\Models\Offer;
use App\Models\ClientRequest;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/feedback', function () {
    if (Auth::guard('client')->check()) {
        return redirect()->route('client.feedback.create');
    }
    return redirect()->route('login')->with('info', app()->getLocale() === 'ar' 
        ? 'يرجى تسجيل الدخول لإرسال ملاحظة' 
        : 'Please login to submit feedback');
})->name('feedback');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale');

// Mobile app WebView: one-time session bridge (signed URL from POST /api/webview-bridge)
Route::get('/app/webview-bridge', [WebviewBridgeController::class, 'establish'])
    ->name('webview.bridge.establish');

// FCM Token Management (for authenticated users - works for both web auth and client auth)
Route::post('/fcm-token', [App\Http\Controllers\FcmTokenController::class, 'update'])->name('fcm-token.update');
Route::delete('/fcm-token', [App\Http\Controllers\FcmTokenController::class, 'remove'])->name('fcm-token.remove');

// Notifications (for authenticated users)
Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/unread', [App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::delete('/notifications', [App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

// User Profile (for system users: Admin, Lab, pharmacy, Nurse)
Route::middleware('auth')->group(function() {
    Route::get('/user/profile', [App\Http\Controllers\UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [App\Http\Controllers\UserProfileController::class, 'update'])->name('user.profile.update');
});

// Auth routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/admin/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Registration routes
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Service provider self-registration (pending admin activation)
Route::get('/service-provider/register', [App\Http\Controllers\Auth\ServiceProviderRegisterController::class, 'select'])->name('service-provider.register');
Route::get('/service-provider/register/{type}', [App\Http\Controllers\Auth\ServiceProviderRegisterController::class, 'create'])->name('service-provider.register.create');
Route::post('/service-provider/register/{type}', [App\Http\Controllers\Auth\ServiceProviderRegisterController::class, 'store'])->name('service-provider.register.store');

Route::prefix('nurse')->name('nurse.')->middleware(['auth'])->group(function () {

    Route::get('offers', [NurseOfferController::class, 'index'])->name('offers.index');
    Route::get('offers/create', [NurseOfferController::class, 'create'])->name('offers.create');
    Route::post('offers', [NurseOfferController::class, 'store'])->name('offers.store');
    Route::get('dashboard', [\App\Http\Controllers\NurseDashboardController::class, 'index'])->name('dashboard');
    Route::get('offers', [\App\Http\Controllers\NurseOfferController::class, 'index'])->name('offers.index');
    Route::get('requests', [\App\Http\Controllers\NurseController::class, 'requestsList'])->name('requests.index');
    Route::get('visits', [\App\Http\Controllers\NurseDashboardController::class, 'index'])->name('visits.index');
    // Nurse visits actions
    Route::put('visits/{visit}/status', [\App\Http\Controllers\NurseVisitController::class, 'updateStatus'])->name('visits.update-status');
    Route::put('visits/{visit}/toggle-paid', [\App\Http\Controllers\NurseVisitController::class, 'togglePaid'])->name('visits.toggle-paid');
    
    // Support Tickets
    Route::get('support-tickets', [App\Http\Controllers\NurseSupportTicketController::class, 'create'])->name('support-tickets.create');
    Route::post('support-tickets', [App\Http\Controllers\NurseSupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('support-tickets/history', [App\Http\Controllers\NurseSupportTicketController::class, 'index'])->name('support-tickets.index');
    });
Route::resource('nurses', App\Http\Controllers\NurseController::class);

// Nurse routes
Route::middleware('auth')->prefix('nurse')->name('nurse.')->group(function () {
	Route::resource('offers', \App\Http\Controllers\NurseOfferController::class)->except(['show']);
});
// Client Dashboard
Route::middleware('auth:client')->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/search/medical-tests', [App\Http\Controllers\ClientDashboardController::class, 'searchMedicalTests'])->name('dashboard.search.medical-tests');
    Route::get('/dashboard/search/laboratories', [App\Http\Controllers\ClientDashboardController::class, 'searchLaboratories'])->name('dashboard.search.laboratories');

    // All requests (pharmacy / lab / nursing)
    Route::get('/my-requests', [App\Http\Controllers\ClientMyRequestsController::class, 'index'])->name('requests.index');
    Route::get('/requests/pharmacy-lab/{clientRequest}', [App\Http\Controllers\ClientMyRequestsController::class, 'showClientRequest'])->name('requests.pharmacy-lab.show');
    Route::get('/requests/pharmacy-lab/{clientRequest}/edit', [App\Http\Controllers\ClientMyRequestsController::class, 'editClientRequest'])->name('requests.pharmacy-lab.edit');
    Route::put('/requests/pharmacy-lab/{clientRequest}', [App\Http\Controllers\ClientMyRequestsController::class, 'updateClientRequest'])->name('requests.pharmacy-lab.update');
    Route::delete('/requests/pharmacy-lab/{clientRequest}', [App\Http\Controllers\ClientMyRequestsController::class, 'destroyClientRequest'])->name('requests.pharmacy-lab.destroy');
    Route::delete('/requests/nurse/{home_nurse_request}', [App\Http\Controllers\ClientMyRequestsController::class, 'destroyNurseRequest'])->name('requests.nurse.destroy');
    Route::get('/requests/clinic/{appointment}', [App\Http\Controllers\ClientMyRequestsController::class, 'showAppointment'])->name('requests.clinic.show');
    Route::get('/requests/clinic/{appointment}/edit', [App\Http\Controllers\ClientMyRequestsController::class, 'editAppointment'])->name('requests.clinic.edit');
    Route::put('/requests/clinic/{appointment}', [App\Http\Controllers\ClientMyRequestsController::class, 'updateAppointment'])->name('requests.clinic.update');
    Route::delete('/requests/clinic/{appointment}', [App\Http\Controllers\ClientMyRequestsController::class, 'cancelAppointment'])->name('requests.clinic.cancel');

    // Test Requests
    Route::get('/test-requests/create/{type}', [App\Http\Controllers\ClientTestRequestController::class, 'create'])->name('test-requests.create');
    Route::post('/test-requests/{type}', [App\Http\Controllers\ClientTestRequestController::class, 'store'])->name('test-requests.store');

    // Medicine Requests (simple create page that posts to test-requests.store with type=medicine)
    Route::get('/medicine-requests/create', function (Request $request) {
        return view('client.medicine-requests.create');
    })->name('medicine-requests.create');

    // Reviews
    Route::post('/reviews', [App\Http\Controllers\ClientReviewController::class, 'store'])->name('reviews.store');

    // Home nurse requests
    Route::get('/nurse-requests', [App\Http\Controllers\ClientNurseRequestController::class, 'index'])->name('nurse-requests.index');
    Route::get('/nurse-visits', [App\Http\Controllers\ClientNurseRequestController::class, 'visitsList'])->name('nurse-visits.index');
    Route::post('visits/{visit}/rate', [App\Http\Controllers\ClientNurseRequestController::class, 'rateVisit'])->name('visits.rate');
    Route::get('/nurse-requests/create', [App\Http\Controllers\ClientNurseRequestController::class, 'create'])->name('nurse-requests.create');
    Route::post('/nurse-requests', [App\Http\Controllers\ClientNurseRequestController::class, 'store'])->name('nurse-requests.store');
    Route::get('/nurse-requests/{home_nurse_request}', [App\Http\Controllers\ClientNurseRequestController::class, 'show'])->name('nurse-requests.show');
    Route::get('/nurse-requests/{home_nurse_request}/edit', [App\Http\Controllers\ClientNurseRequestController::class, 'edit'])->name('nurse-requests.edit');
    Route::put('/nurse-requests/{home_nurse_request}', [App\Http\Controllers\ClientNurseRequestController::class, 'update'])->name('nurse-requests.update');

    // Nurse offers actions
    Route::put('/nurse-offers/{nurse_offer}/accept', [App\Http\Controllers\ClientNurseRequestController::class, 'acceptOffer'])->name('nurse-offers.accept');
    Route::put('/nurse-offers/{nurse_offer}/reject', [App\Http\Controllers\ClientNurseRequestController::class, 'rejectOffer'])->name('nurse-offers.reject');
    // Offers
    Route::get('/offers', [App\Http\Controllers\ClientOfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/get', [App\Http\Controllers\ClientOfferController::class, 'getOffers'])->name('offers.get');
    Route::put('/offers/{offer}/accept', [App\Http\Controllers\ClientOfferController::class, 'accept'])->name('offers.accept');
    Route::put('/offers/{offer}/reject', [App\Http\Controllers\ClientOfferController::class, 'reject'])->name('offers.reject');
    Route::post('/offers/direct', [App\Http\Controllers\ClientOfferController::class, 'createDirectOffer'])->name('offers.direct');

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
    Route::delete('/profile', [App\Http\Controllers\ClientProfileController::class, 'destroy'])->name('profile.destroy');

    // Feedback
    Route::get('/feedback', [App\Http\Controllers\ClientFeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [App\Http\Controllers\ClientFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/history', [App\Http\Controllers\ClientFeedbackController::class, 'index'])->name('feedback.index');

    // Test Results
    Route::get('/test-results', [App\Http\Controllers\ClientTestResultController::class, 'index'])->name('test-results.index');

    // Orders
    Route::get('/orders', [App\Http\Controllers\ClientOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/review', [App\Http\Controllers\ClientOrderController::class, 'storeReview'])->name('orders.review');

    // Laboratories
    Route::get('/laboratories', [App\Http\Controllers\ClientLaboratoryController::class, 'index'])->name('laboratories.index');
    Route::get('/laboratories/{laboratory}/offers', [App\Http\Controllers\ClientLaboratoryController::class, 'offers'])->name('laboratories.offers');

    // Service Providers Search (Laboratories & Pharmacies)
    Route::get('/service-providers', [App\Http\Controllers\ClientServiceProviderController::class, 'index'])->name('service-providers.index');

    // Quotes
    Route::get('/quotes', [App\Http\Controllers\ClientQuoteController::class, 'index'])->name('quotes.index');
    Route::post('/quotes', [App\Http\Controllers\ClientQuoteController::class, 'store'])->name('quotes.store');

    // Doctor / Clinic reservation (حجز المواعيد)
    Route::get('/doctor-reservation', [App\Http\Controllers\ClientDoctorReservationController::class, 'index'])->name('doctor-reservation.index');
    Route::get('/doctor-reservation/book/{clinic}', [App\Http\Controllers\ClientDoctorReservationController::class, 'book'])->name('doctor-reservation.book');
    Route::post('/doctor-reservation', [App\Http\Controllers\ClientDoctorReservationController::class, 'store'])->name('doctor-reservation.store');
    Route::get('/doctor-reservation/available-slots', [App\Http\Controllers\ClientDoctorReservationController::class, 'availableSlots'])->name('doctor-reservation.available-slots');
});

// Doctor Dashboard (auth + must have doctor profile)
Route::middleware(['auth', 'doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Doctor\DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [App\Http\Controllers\Doctor\DoctorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Doctor\DoctorProfileController::class, 'update'])->name('profile.update');
    Route::get('/clinics', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'index'])->name('clinics.index');
    Route::get('/clinics/create', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'create'])->name('clinics.create');
    Route::post('/clinics', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'store'])->name('clinics.store');
    Route::get('/clinics/{clinic}', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'show'])->name('clinics.show');
    Route::get('/clinics/{clinic}/edit', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'edit'])->name('clinics.edit');
    Route::put('/clinics/{clinic}', [App\Http\Controllers\Doctor\DoctorClinicController::class, 'update'])->name('clinics.update');
    Route::get('/appointments', [App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'index'])->name('appointments.index');
    Route::put('/appointments/{appointment}/status', [App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::get('/calendar', [App\Http\Controllers\Doctor\DoctorCalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/toggle-off', [App\Http\Controllers\Doctor\DoctorCalendarController::class, 'toggleOff'])->name('calendar.toggle-off');
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

    Route::post('/offers/{offer}/mark-paid', [App\Http\Controllers\LaboratoryOfferController::class, 'markAsPaid'])->name('offers.mark-paid');

    // Requests
    Route::get('/requests', [App\Http\Controllers\LaboratoryRequestController::class, 'index'])->name('requests.index');

    // Offers
    Route::get('/offers', [App\Http\Controllers\LaboratoryOfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/accepted', [App\Http\Controllers\LaboratoryOfferController::class, 'accepted'])->name('offers.accepted');
    Route::put('/offers/{offer}/cancel', [App\Http\Controllers\LaboratoryOfferController::class, 'cancel'])->name('offers.cancel');
    Route::put('/offers/{offer}/vendor-status', [App\Http\Controllers\LaboratoryOfferController::class, 'updateVendorStatus'])->name('offers.update-vendor-status');
    Route::post('/offers/{offer}/attachments', [App\Http\Controllers\LaboratoryOfferController::class, 'uploadAttachment'])->name('offers.upload-attachment');
    Route::delete('/offers/{offer}/attachments/{attachment}', [App\Http\Controllers\LaboratoryOfferController::class, 'deleteAttachment'])->name('offers.delete-attachment');

    // Quotes
    Route::get('/quotes', [App\Http\Controllers\LaboratoryQuoteController::class, 'index'])->name('quotes.index');
    Route::put('/quotes/{quote}', [App\Http\Controllers\LaboratoryQuoteController::class, 'update'])->name('quotes.update');

    // Profile
    Route::get('/profile/edit', [App\Http\Controllers\LaboratoryProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{laboratory}', [App\Http\Controllers\LaboratoryProfileController::class, 'update'])->name('profile.update');

    // Users
    Route::get('/users/data', [App\Http\Controllers\LaboratoryUserController::class, 'data'])->name('users.data');
    Route::resource('users', App\Http\Controllers\LaboratoryUserController::class);

    // Test Prices
    Route::get('/test-prices', [App\Http\Controllers\LaboratoryTestPriceController::class, 'index'])->name('test-prices.index');
    Route::post('/test-prices/store-or-update', [App\Http\Controllers\LaboratoryTestPriceController::class, 'storeOrUpdate'])->name('test-prices.store-or-update');

    // Support Tickets
    Route::get('/support-tickets', [App\Http\Controllers\LaboratorySupportTicketController::class, 'create'])->name('support-tickets.create');
    Route::post('/support-tickets', [App\Http\Controllers\LaboratorySupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/history', [App\Http\Controllers\LaboratorySupportTicketController::class, 'index'])->name('support-tickets.index');
});

// Clinic staff dashboard (clinic manager account)
Route::middleware(['auth', 'clinic_staff'])->prefix('clinic')->name('clinic.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Clinic\ClinicDashboardController::class, 'index'])->name('dashboard');
});

// Pharmacy Dashboard Routes
Route::middleware('auth')->prefix('pharmacy')->name('pharmacies.')->group(function () {
	Route::get('/dashboard', [App\Http\Controllers\PharmacyDashboardController::class, 'index'])->name('dashboard');
	// Requests
	Route::get('/requests', [App\Http\Controllers\PharmacyRequestController::class, 'index'])->name('requests.index');
	// Offers
	Route::get('/offers', [App\Http\Controllers\PharmacyOfferController::class, 'index'])->name('offers.index');
	Route::get('/offers/accepted', [App\Http\Controllers\PharmacyOfferController::class, 'accepted'])->name('offers.accepted');
	// Orders
	Route::get('/orders', [App\Http\Controllers\PharmacyOrderController::class, 'index'])->name('orders.index');
	Route::put('/orders/{order}/status', [App\Http\Controllers\PharmacyOrderController::class, 'updateStatus'])->name('orders.update-status');
	Route::put('/orders/{order}/mark-paid', [App\Http\Controllers\PharmacyOrderController::class, 'markPaid'])->name('orders.mark-paid');
	// Pharmacy Profile
	Route::get('/profile/edit', [App\Http\Controllers\PharmacyProfileController::class, 'edit'])->name('profile.edit');
	Route::put('/profile/{pharmacy}', [App\Http\Controllers\PharmacyProfileController::class, 'update'])->name('profile.update');
});

// Admin routes (Blade-based admin panel)
Route::middleware([
    'auth',
    \App\Http\Middleware\RedirectLaboratoryOwner::class,
    \App\Http\Middleware\RedirectPharmacyOwner::class,
    \App\Http\Middleware\RedirectClinicStaff::class,
])->prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin to /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Areas
    Route::resource('areas', App\Http\Controllers\Admin\AreaController::class);

    // Cities
    Route::resource('cities', App\Http\Controllers\Admin\CityController::class);

    // Governorates
    Route::resource('governorates', App\Http\Controllers\Admin\GovernorateController::class);

    // Medical Tests
    Route::get('/medical-tests/data', [App\Http\Controllers\Admin\MedicalTestController::class, 'data'])->name('medical-tests.data');
    Route::resource('medical-tests', App\Http\Controllers\Admin\MedicalTestController::class);

    // Medicines
    Route::get('/medicines/data', [App\Http\Controllers\Admin\MedicineController::class, 'data'])->name('medicines.data');
    Route::resource('medicines', App\Http\Controllers\Admin\MedicineController::class);

    // Users
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);

    // Pharmacies
    Route::resource('pharmacies', App\Http\Controllers\Admin\PharmacyController::class);

    // Laboratories
    Route::get('/laboratories', [App\Http\Controllers\Admin\LaboratoryController::class, 'index'])->name('laboratories.index');
    Route::get('/laboratories/create', [App\Http\Controllers\Admin\LaboratoryController::class, 'create'])->name('laboratories.create');
    Route::post('/laboratories', [App\Http\Controllers\Admin\LaboratoryController::class, 'store'])->name('laboratories.store');
    Route::get('/laboratories/{laboratory}/edit', [App\Http\Controllers\Admin\LaboratoryController::class, 'edit'])->name('laboratories.edit');
    Route::put('/laboratories/{laboratory}', [App\Http\Controllers\Admin\LaboratoryController::class, 'update'])->name('laboratories.update');
    Route::patch('/laboratories/{laboratory}/toggle-active', [App\Http\Controllers\Admin\LaboratoryController::class, 'toggleActive'])->name('laboratories.toggle-active');
    Route::delete('/laboratories/{laboratory}', [App\Http\Controllers\Admin\LaboratoryController::class, 'destroy'])->name('laboratories.destroy');

    // Nurses
    Route::resource('nurses', App\Http\Controllers\Admin\NurseController::class);

    // Clients
    Route::resource('clients', App\Http\Controllers\Admin\ClientController::class);

    // Feedback
    Route::get('/feedback', [App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [App\Http\Controllers\Admin\FeedbackController::class, 'show'])->name('feedback.show');
    Route::put('/feedback/{feedback}', [App\Http\Controllers\Admin\FeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [App\Http\Controllers\Admin\FeedbackController::class, 'destroy'])->name('feedback.destroy');

    // Support Tickets
    Route::get('/support-tickets', [App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{supportTicket}', [App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::put('/support-tickets/{supportTicket}', [App\Http\Controllers\Admin\SupportTicketController::class, 'update'])->name('support-tickets.update');
    Route::delete('/support-tickets/{supportTicket}', [App\Http\Controllers\Admin\SupportTicketController::class, 'destroy'])->name('support-tickets.destroy');

    // Client Requests
    Route::resource('client-requests', App\Http\Controllers\Admin\ClientRequestController::class);

    // Orders
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);

    // Offers
    Route::resource('offers', App\Http\Controllers\Admin\OfferController::class);

    // Medical Test Offers
    Route::get('/medical-test-offers/data', [App\Http\Controllers\Admin\MedicalTestOfferController::class, 'data'])->name('medical-test-offers.data');
    Route::resource('medical-test-offers', App\Http\Controllers\Admin\MedicalTestOfferController::class);

    // Bonus Points
    Route::get('/bonus-points', [App\Http\Controllers\Admin\BonusPointController::class, 'index'])->name('bonus-points.index');

    // Charitable Organizations
    Route::resource('charitable-organizations', App\Http\Controllers\Admin\CharitableOrganizationController::class);

    // Specializations (for doctors)
    Route::resource('specializations', App\Http\Controllers\Admin\SpecializationController::class);

    // Doctors, Clinics, Appointments
    Route::resource('doctors', App\Http\Controllers\Admin\DoctorController::class);

    Route::resource('clinics', App\Http\Controllers\Admin\ClinicController::class);

    Route::get('/appointments/available-slots', [App\Http\Controllers\Admin\AppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
    Route::resource('appointments', App\Http\Controllers\Admin\AppointmentController::class);
});
// Add this after your admin routes or create a separate nurse group



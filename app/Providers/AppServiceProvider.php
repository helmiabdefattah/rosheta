<?php

namespace App\Providers;

use App\Models\DentalLabPayment;
use App\Observers\DentalLabPaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mirror clinic→lab payments into the expenses ledger for clinics that
        // opted into auto-capture (the observer no-ops otherwise).
        DentalLabPayment::observe(DentalLabPaymentObserver::class);
    }
}

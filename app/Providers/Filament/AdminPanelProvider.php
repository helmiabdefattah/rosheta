<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
//            ->topbar(false)
//            ->databaseNotifications()
//            ->databaseNotificationsPolling('30s')
        ->default()
            ->id('admin')
            ->path('filament-admin') // Changed to avoid conflict with Blade admin routes
            ->login(false) // Disable Filament login, using custom Blade login
            ->authGuard('web')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->brandLogo(fn () => view('filament.brand-logo'))

            ->favicon(url('/images/mo-logo.png'))
            // Disabled: Using Blade-based admin panel instead
            // ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->brandName('Mostashfa-on')
            // ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Dashboard::class, // Using custom Blade dashboard
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // Removed RedirectLaboratoryOwner - using Blade admin panel now
            ])
            ->authMiddleware([
                \App\Http\Middleware\AuthenticateAdmin::class,
            ]);
    }
}

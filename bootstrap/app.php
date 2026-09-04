<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Route middleware alias for selective CORS
        $middleware->alias([
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'doctor' => \App\Http\Middleware\EnsureUserIsDoctor::class,
            'clinic_staff' => \App\Http\Middleware\EnsureUserIsClinicStaff::class,
            'clinic.role' => \App\Http\Middleware\EnsureClinicRole::class,
            'client.api' => \App\Http\Middleware\EnsureAuthenticatedClient::class,
        ]);

        // The demo switch MUST run before StartSession and Authenticate:
        // it repoints the default database connection (and the session store)
        // at the demo database. Anything later would already have read the
        // session and the user from production. See StartDemoSession.
        $middleware->prependToGroup('web', \App\Http\Middleware\StartDemoSession::class);

        // Read before EncryptCookies runs, so it carries its own HMAC instead.
        $middleware->encryptCookies(except: [
            'mo_demo',
        ]);

        // Starting a demo is the one place where CSRF cannot apply: the token
        // on the login page belongs to a session in the PRODUCTION database,
        // while this request already reads sessions from the demo database, so
        // the token could never match. There is no user session to protect at
        // this point either — the endpoint is anonymous by design. Abuse is
        // handled by the per-IP and global limits in DemoController.
        // Every other demo route runs inside the demo session and keeps CSRF.
        $middleware->validateCsrfTokens(except: [
            'demo/start',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\MostashfaOnClientContext::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\MostashfaOnClientContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

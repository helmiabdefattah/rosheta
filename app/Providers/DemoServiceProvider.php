<?php

namespace App\Providers;

use App\Demo\DemoContext;
use App\Demo\GuardedMySqlConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class DemoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One answer to "are we in a demo?" per request, shared by the
        // middleware, the write guard, the seeder and the views.
        $this->app->singleton(DemoContext::class);

        // Route every MySQL connection through the guarded subclass, so a
        // demo request that somehow escapes the connection switch is stopped
        // BEFORE the write reaches production rather than reported after.
        // Registered in register() because it must be in place before the
        // first connection is resolved.
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new GuardedMySqlConnection($connection, $database, $prefix, $config);
        });
    }

    public function boot(): void
    {
        // Views (the login button, the demo bar) ask this without needing to
        // resolve the container themselves.
        view()->composer('*', function ($view) {
            $view->with('demoContext', $this->app->make(DemoContext::class));
        });
    }
}

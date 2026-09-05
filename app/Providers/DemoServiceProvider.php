<?php

namespace App\Providers;

use App\Demo\DemoContext;
use App\Demo\GuardedMySqlConnection;
use App\Demo\SpecialtyProfile;
use App\Models\Specialization;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
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

        // The specialization picker inside the demo card. Bound to the card
        // itself rather than to the pages that show it — the login page, the
        // landing page, whatever comes next — so including the card is the
        // whole of what a page has to do. A composer rather than a controller,
        // so no production code path changes.
        view()->composer('demo.start-card', function ($view) {
            $view->with('demoSpecializations', $this->specializationChoices());
        });
    }

    /**
     * Specializations for the demo picker, split into those with a purpose-built
     * clinic and those that fall back to general content — so the doctor knows
     * what they are getting before they press the button.
     *
     * @return array{tailored: \Illuminate\Support\Collection, general: \Illuminate\Support\Collection}
     */
    protected function specializationChoices(): array
    {
        $empty = ['tailored' => collect(), 'general' => collect()];

        if (! config('demo.enabled')) {
            return $empty;
        }

        try {
            $profiled = array_keys(SpecialtyProfile::all());

            $all = Specialization::query()
                ->select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get();

            return [
                'tailored' => $all->filter(fn ($s) => in_array($s->slug, $profiled, true))->values(),
                'general' => $all->reject(fn ($s) => in_array($s->slug, $profiled, true))->values(),
            ];
        } catch (\Throwable $e) {
            // A missing table or an unreadable profile must never take the
            // login page down.
            Log::warning('Demo specialization picker unavailable', ['error' => $e->getMessage()]);

            return $empty;
        }
    }
}

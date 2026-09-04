<?php

namespace App\Console\Commands;

use App\Demo\DemoPurger;
use App\Models\DemoSession;
use Illuminate\Console\Command;

/**
 * Removes demo tenants whose session has ended, expired or gone idle.
 *
 * Runs on a schedule (see routes/console.php) and can be invoked by hand:
 *
 *   php artisan demo:purge                     -- everything due
 *   php artisan demo:purge --all               -- every demo tenant, due or not
 *   php artisan demo:purge --session=<uuid>    -- one session
 *   php artisan demo:purge --doctor=<id>       -- one tenant, even with no session
 */
class DemoPurgeCommand extends Command
{
    protected $signature = 'demo:purge
        {--all : Purge every demo tenant, including sessions that are still active}
        {--session= : Purge a single demo session by id}
        {--doctor= : Purge a single demo tenant by doctor id}';

    protected $description = 'Hard-delete expired, idle or finished demo tenants';

    public function handle(DemoPurger $purger): int
    {
        if (! config('demo.enabled') && ! $this->option('all')) {
            $this->warn('Demo is disabled (DEMO_ENABLED=false). Use --all to purge leftovers anyway.');
        }

        if ($doctorId = $this->option('doctor')) {
            $deleted = $purger->purgeDoctor((int) $doctorId);
            $this->report("doctor #{$doctorId}", $deleted);

            return self::SUCCESS;
        }

        $sessions = $this->sessions();

        if ($sessions->isEmpty()) {
            $this->info('Nothing to purge.');

            return self::SUCCESS;
        }

        $this->info("Purging {$sessions->count()} demo tenant(s) …");

        $failures = 0;

        foreach ($sessions as $session) {
            try {
                $deleted = $purger->purgeSession($session, $session->expiryReason() ?? 'purged');
                $this->report((string) $session->id, $deleted);
            } catch (\Throwable $e) {
                $failures++;
                $this->error("  {$session->id}: {$e->getMessage()}");
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return \Illuminate\Support\Collection<int, DemoSession> */
    protected function sessions()
    {
        if ($id = $this->option('session')) {
            return DemoSession::whereKey($id)->get();
        }

        if ($this->option('all')) {
            return DemoSession::whereNotNull('doctor_id')->get();
        }

        return DemoSession::purgeable()->get();
    }

    /** @param  array<string,int>  $deleted */
    protected function report(string $label, array $deleted): void
    {
        $rows = array_sum($deleted);
        $tables = count(array_filter($deleted));

        $this->line("  {$label}: {$rows} rows across {$tables} tables");
    }
}

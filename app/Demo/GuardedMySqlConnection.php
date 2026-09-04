<?php

namespace App\Demo;

use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\Log;

/**
 * DEMO_PROD_WRITE_GUARD.
 *
 * Having a second connection is not the guarantee — a single forgotten model
 * would silently write to production. This connection class refuses the write
 * instead of reporting it afterwards, which is why the guard lives here rather
 * than in DB::listen (that fires only once the query has already run).
 *
 * Every MySQL connection is resolved through this class (see
 * DemoServiceProvider). It only intervenes when a demo request is in flight
 * AND the connection being written to is not the demo one.
 */
class GuardedMySqlConnection extends MySqlConnection
{
    /** SQL verbs that modify data or structure. */
    private const WRITE_VERBS = 'insert|update|delete|replace|alter|truncate|drop|create|rename|grant|revoke';

    /**
     * MySqlConnection overrides insert() and runs the query itself instead of
     * delegating to statement(), so guarding statement() alone would let every
     * INSERT through. Each write entry point is therefore guarded explicitly.
     */
    public function insert($query, $bindings = [], $sequence = null)
    {
        $this->guard($query);

        return parent::insert($query, $bindings, $sequence);
    }

    public function update($query, $bindings = [])
    {
        $this->guard($query);

        return parent::update($query, $bindings);
    }

    public function delete($query, $bindings = [])
    {
        $this->guard($query);

        return parent::delete($query, $bindings);
    }

    public function statement($query, $bindings = [])
    {
        $this->guard($query);

        return parent::statement($query, $bindings);
    }

    public function affectingStatement($query, $bindings = [])
    {
        $this->guard($query);

        return parent::affectingStatement($query, $bindings);
    }

    public function unprepared($query)
    {
        $this->guard($query);

        return parent::unprepared($query);
    }

    /**
     * Block the query if a demo request is writing outside the demo database.
     *
     * @throws DemoProductionWriteException
     */
    protected function guard(string $query): void
    {
        if (! config('demo.prod_write_guard', true)) {
            return;
        }

        // Never resolve the context if the container has not booted it — this
        // runs for console commands and migrations too.
        if (! app()->bound(DemoContext::class)) {
            return;
        }

        if (! app(DemoContext::class)->isDemo()) {
            return;
        }

        // Writes on the demo connection itself are the whole point.
        if ($this->getName() === config('demo.connection')) {
            return;
        }

        if (! $this->isWrite($query)) {
            return;
        }

        if ($this->isAllowListed($query)) {
            return;
        }

        Log::critical('Demo request attempted a production write', [
            'connection' => $this->getName(),
            'sql' => $query,
            'demo_session' => app(DemoContext::class)->sessionId(),
        ]);

        throw new DemoProductionWriteException($query, (string) $this->getName());
    }

    protected function isWrite(string $query): bool
    {
        return (bool) preg_match('/^\s*(?:'.self::WRITE_VERBS.')\b/i', $query);
    }

    /**
     * The only production writes a demo request may make are its own funnel
     * records, which must outlive the tenant.
     */
    protected function isAllowListed(string $query): bool
    {
        foreach ((array) config('demo.prod_write_allowlist', []) as $table) {
            if (preg_match('/[`"\s.(]'.preg_quote($table, '/').'[`"\s.(]/i', $query.' ')) {
                return true;
            }
        }

        return false;
    }
}

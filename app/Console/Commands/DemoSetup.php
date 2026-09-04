<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Builds (or refreshes) the demo database.
 *
 * The structure is copied from production with SHOW CREATE TABLE rather than
 * by running migrations, for two reasons:
 *
 *  1. `php artisan migrate` currently cannot rebuild this schema from scratch
 *     — six migration files were deleted after being run, and nothing on disk
 *     creates `medical_tests`, so a fresh migrate dies at migration 18 of 132.
 *     (See docs/demo-sandbox/DISCOVERY.md, Q8.)
 *  2. SHOW CREATE TABLE reproduces foreign keys exactly, which CREATE TABLE
 *     ... LIKE does not — and the purge depends on those cascades.
 *
 * Once the migration pipeline is repaired, this can be replaced with
 * `php artisan migrate --database=demo`.
 */
class DemoSetup extends Command
{
    protected $signature = 'demo:setup
        {--fresh : Drop the demo database first and rebuild it from scratch}
        {--structure-only : Skip copying reference data}';

    protected $description = 'Create the demo database: structure from production + reference data';

    public function handle(): int
    {
        $demo = config('demo.connection');
        $database = config("database.connections.{$demo}.database");

        if (! $database) {
            $this->error("No database configured for connection [{$demo}]. Set DEMO_DB_DATABASE.");

            return self::FAILURE;
        }

        $productionDatabase = DB::connection('mysql')->getDatabaseName();

        if ($database === $productionDatabase) {
            $this->error("The demo database must not be the production database [{$database}].");

            return self::FAILURE;
        }

        $this->info("Production : {$productionDatabase}");
        $this->info("Demo       : {$database}");

        if ($this->option('fresh')) {
            $this->warn("Dropping {$database} …");
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$database}`");
        }

        DB::connection('mysql')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        DB::purge($demo);

        $this->copyStructure($productionDatabase, $demo);

        if (! $this->option('structure-only')) {
            $this->copyReferenceData($demo);
            $this->sanitizeReferences($demo);
        }

        $this->newLine();
        $this->info('Demo database ready.');

        return self::SUCCESS;
    }

    /** Recreate every production table (minus the skipped ones) on the demo connection. */
    protected function copyStructure(string $productionDatabase, string $demo): void
    {
        $skip = (array) config('demo.skip_tables', []);

        $tables = collect(DB::connection('mysql')->select(
            'SELECT TABLE_NAME t FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$productionDatabase]
        ))->pluck('t')->reject(fn ($t) => in_array($t, $skip, true))->values();

        $this->newLine();
        $this->info("Copying structure for {$tables->count()} tables …");

        $connection = DB::connection($demo);
        $connection->statement('SET FOREIGN_KEY_CHECKS = 0');

        $bar = $this->output->createProgressBar($tables->count());

        foreach ($tables as $table) {
            $ddl = (array) DB::connection('mysql')->select("SHOW CREATE TABLE `{$table}`")[0];
            $create = $ddl['Create Table'] ?? $ddl['Create View'] ?? null;

            if ($create === null) {
                $this->warn("  skipped {$table}: not a table");
                continue;
            }

            $connection->statement("DROP TABLE IF EXISTS `{$table}`");
            $connection->unprepared($create);

            $bar->advance();
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS = 1');

        $bar->finish();
        $this->newLine();
    }

    /**
     * One-way copy of reference data, production -> demo.
     *
     * Refuses anything on the forbidden list even if someone adds it to
     * DEMO_REFERENCE_TABLES: patients, visits, prescriptions, invoices and
     * real user tables must never be copied. That is a legal requirement,
     * not a preference.
     */
    protected function copyReferenceData(string $demo): void
    {
        $forbidden = (array) config('demo.reference_forbidden', []);
        $tables = (array) config('demo.reference_tables', []);

        $this->newLine();
        $this->info('Copying reference data …');

        foreach ($tables as $table) {
            $table = trim($table);

            if ($table === '') {
                continue;
            }

            if (in_array($table, $forbidden, true)) {
                $this->error("  REFUSED {$table}: contains patient or user data and may never be copied.");

                continue;
            }

            if (! DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->warn("  skipped {$table}: not present in production");

                continue;
            }

            $this->copyTable($table, $demo);
        }
    }

    /**
     * Repair references that mirroring inevitably breaks.
     *
     * `laboratories.user_id` points at the lab's owner account in production —
     * a `users` row we deliberately do NOT copy. Left as-is it is a dangling
     * id, and the onboarding seeder uses it as `offers.user_id`, which fails
     * the foreign key and takes the whole demo start down with it.
     *
     * So the demo database gets one inert infrastructure account that mirrored
     * labs belong to. It has no usable password, is inactive, and belongs to no
     * tenant — the purge never touches it.
     */
    protected function sanitizeReferences(string $demo): void
    {
        $target = DB::connection($demo);

        if (! $target->getSchemaBuilder()->hasTable('laboratories')) {
            return;
        }

        $this->newLine();
        $this->info('Repairing mirrored references …');

        $email = 'demo-lab-owner@demo.invalid';
        $userId = $target->table('users')->where('email', $email)->value('id');

        if ($userId === null) {
            $userId = $target->table('users')->insertGetId([
                'name' => 'Demo Marketplace Account',
                'email' => $email,
                'password' => bcrypt(bin2hex(random_bytes(24))),
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $updated = $target->table('laboratories')->update(['user_id' => $userId]);

        $this->line("  laboratories.user_id -> demo account #{$userId} ({$updated} rows)");
    }

    /**
     * Batched copy, so a large table (medicines is ~25k rows) never locks
     * production and no single INSERT exceeds MySQL's 65,535-placeholder
     * limit — which a wide table reaches surprisingly quickly.
     */
    protected function copyTable(string $table, string $demo): void
    {
        $source = DB::connection('mysql');
        $target = DB::connection($demo);

        $columns = count($source->getSchemaBuilder()->getColumnListing($table)) ?: 1;
        $readChunk = 2000;
        $insertChunk = max(1, min($readChunk, intdiv(60000, $columns)));

        $target->statement('SET FOREIGN_KEY_CHECKS = 0');
        $target->table($table)->truncate();

        $copied = 0;

        $source->table($table)->orderBy('id')->chunk($readChunk, function ($rows) use ($target, $table, $insertChunk, &$copied) {
            foreach ($rows->map(fn ($row) => (array) $row)->chunk($insertChunk) as $batch) {
                $target->table($table)->insert($batch->all());
            }

            $copied += $rows->count();
        });

        $target->statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->line("  {$table}: {$copied} rows");
    }
}

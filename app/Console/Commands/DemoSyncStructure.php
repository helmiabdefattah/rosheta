<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings the demo database's structure up to production's WITHOUT dropping
 * anything — the additive counterpart to demo:setup.
 *
 * demo:setup mirrors the schema by dropping and recreating every table, which
 * also destroys whatever demo tenants are mid-session. That is the right tool
 * for a first build or a deliberate rebuild, and the wrong one for "main added
 * two migrations and now the demo 500s on a missing table".
 *
 * This command instead creates the tables production has and demo lacks, and
 * adds missing columns using production's own column definition (read out of
 * SHOW CREATE TABLE, so defaults, collations and comments come across exactly).
 * Running demos keep their data.
 *
 * What it deliberately does NOT do: drop tables or columns the demo has and
 * production does not, or alter an existing column whose type has changed.
 * Both are destructive and rare enough to be worth a human's attention — the
 * command reports them and tells you to run demo:setup.
 *
 * Run it after every `php artisan migrate` that touches a mirrored table.
 */
class DemoSyncStructure extends Command
{
    protected $signature = 'demo:sync-structure
        {--apply : Execute the statements (without this it only reports them)}';

    protected $description = 'Add tables and columns the demo database is missing, without dropping demo data';

    public function handle(): int
    {
        $demoConnection = config('demo.connection');
        $demoDatabase = config("database.connections.{$demoConnection}.database");

        if (! $demoDatabase) {
            $this->error("No database configured for connection [{$demoConnection}]. Set DEMO_DB_DATABASE.");

            return self::FAILURE;
        }

        $production = DB::connection('mysql')->getDatabaseName();

        if ($demoDatabase === $production) {
            $this->error("The demo database must not be the production database [{$demoDatabase}].");

            return self::FAILURE;
        }

        $this->info("Production : {$production}");
        $this->info("Demo       : {$demoDatabase}");
        $this->newLine();

        $statements = array_merge(
            $this->missingTables($production, $demoDatabase),
            $this->missingColumns($production, $demoDatabase),
        );

        $this->reportDrift($production, $demoDatabase);

        if (! $statements) {
            $this->info('Demo structure already matches production.');

            return self::SUCCESS;
        }

        foreach ($statements as [$table, $sql]) {
            $this->line("  <fg=yellow>{$table}</>: ".$this->summarize($sql));
        }

        $this->newLine();

        if (! $this->option('apply')) {
            $this->warn(count($statements).' statement(s) pending. Re-run with --apply to execute.');

            return self::SUCCESS;
        }

        $demo = DB::connection($demoConnection);
        $demo->statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($statements as [$table, $sql]) {
                $demo->unprepared($sql);
            }
        } finally {
            $demo->statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        $this->info('Applied '.count($statements).' statement(s). Demo structure is up to date.');

        return self::SUCCESS;
    }

    /** Tables production has that the demo lacks, copied verbatim (foreign keys included). */
    protected function missingTables(string $production, string $demoDatabase): array
    {
        $skip = (array) config('demo.skip_tables', []);
        $missing = array_diff($this->tables($production), $this->tables($demoDatabase), $skip);

        $statements = [];

        foreach ($missing as $table) {
            if ($create = $this->createStatement($table)) {
                $statements[] = [$table, $create];
            }
        }

        return $statements;
    }

    /**
     * Columns production has that the demo lacks, added with production's exact
     * definition and in production's position — so the demo table reads the
     * same as the real one when someone compares them.
     */
    protected function missingColumns(string $production, string $demoDatabase): array
    {
        $statements = [];

        foreach (array_intersect($this->tables($production), $this->tables($demoDatabase)) as $table) {
            $productionColumns = $this->columns($production, $table);
            $missing = array_diff($productionColumns, $this->columns($demoDatabase, $table));

            if (! $missing) {
                continue;
            }

            $create = $this->createStatement($table);

            foreach ($missing as $column) {
                $definition = $this->columnDefinition($create, $column);

                if ($definition === null) {
                    $this->warn("  could not read the definition of {$table}.{$column} — skipped");

                    continue;
                }

                $position = array_search($column, $productionColumns, true);
                $after = $position > 0
                    ? ' AFTER `'.$productionColumns[$position - 1].'`'
                    : ' FIRST';

                $statements[] = [$table, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}{$after}"];
            }
        }

        return $statements;
    }

    /**
     * Differences this command will not touch, because fixing them means
     * dropping or rewriting something. Reported so they cannot pass unnoticed.
     */
    protected function reportDrift(string $production, string $demoDatabase): void
    {
        $skip = (array) config('demo.skip_tables', []);
        $extraTables = array_diff($this->tables($demoDatabase), $this->tables($production), $skip);

        foreach ($extraTables as $table) {
            $this->warn("  {$table}: exists in demo but not in production — run demo:setup to rebuild.");
        }

        foreach (array_intersect($this->tables($production), $this->tables($demoDatabase)) as $table) {
            $extraColumns = array_diff($this->columns($demoDatabase, $table), $this->columns($production, $table));

            foreach ($extraColumns as $column) {
                $this->warn("  {$table}.{$column}: exists in demo but not in production — run demo:setup to rebuild.");
            }
        }
    }

    /** @return array<int, string> */
    protected function tables(string $database): array
    {
        return collect(DB::connection('mysql')->select(
            'SELECT TABLE_NAME t FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$database]
        ))->pluck('t')->all();
    }

    /** Column names in declaration order. @return array<int, string> */
    protected function columns(string $database, string $table): array
    {
        return collect(DB::connection('mysql')->select(
            'SELECT COLUMN_NAME c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$database, $table]
        ))->pluck('c')->all();
    }

    protected function createStatement(string $table): ?string
    {
        $row = (array) DB::connection('mysql')->select("SHOW CREATE TABLE `{$table}`")[0];

        return $row['Create Table'] ?? null;
    }

    /**
     * One column's definition, lifted from the table's own CREATE statement
     * rather than rebuilt from information_schema — that way the default,
     * collation, generated expression and comment come across as written.
     */
    protected function columnDefinition(?string $create, string $column): ?string
    {
        if ($create === null) {
            return null;
        }

        $pattern = '/^\s*`'.preg_quote($column, '/').'`\s+(.+?),?$/m';

        return preg_match($pattern, $create, $matches) ? rtrim($matches[1], ',') : null;
    }

    /** CREATE TABLE statements are long; the table name is the useful part. */
    protected function summarize(string $sql): string
    {
        $oneLine = preg_replace('/\s+/', ' ', $sql);

        return str_starts_with($oneLine, 'CREATE TABLE')
            ? 'CREATE TABLE (new)'
            : str_replace('ALTER TABLE ', '', $oneLine);
    }
}

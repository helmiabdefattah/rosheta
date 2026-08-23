<?php

namespace App\Console\Commands;

use App\Services\ProvidersNetworkService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * CLI wrapper around ProvidersNetworkService: search the external providers
 * directory and import the matching doctors/clinics. The admin screen
 * (admin.providers-network.index) does the same interactively.
 *
 * Examples:
 *   php artisan providers:import-network --governorate=القليوبية --type=عيادات
 *   php artisan providers:import-network --governorate=القاهرة --type=اسنان --dry-run
 */
class ImportProvidersNetwork extends Command
{
    protected $signature = 'providers:import-network
        {--governorate= : Governorate (Arabic), e.g. القليوبية}
        {--city= : City (Arabic), e.g. قليوب}
        {--type= : provider_type (Arabic), e.g. عيادات}
        {--search= : search_query (by name)}
        {--dry-run : Show what would be imported without writing}';

    protected $description = 'Search & import doctors/clinics from the providers-network API';

    public function handle(ProvidersNetworkService $service): int
    {
        $filters = [
            'governorate' => (string) $this->option('governorate'),
            'city' => (string) $this->option('city'),
            'provider_type' => (string) $this->option('type'),
            'search_query' => (string) $this->option('search'),
        ];

        if (! collect($filters)->first(fn ($v) => trim((string) $v) !== '')) {
            $this->error('Provide at least one filter: --governorate, --city, --type, or --search.');

            return self::FAILURE;
        }

        $this->info('Searching the providers-network API…');
        $data = $service->search($filters);

        if (($data['error'] ?? null)) {
            $this->error('API error: '.$data['error']);

            return self::FAILURE;
        }

        $rows = $data['results'] ?? [];
        $this->line('Matching providers: '.count($rows));

        if (empty($rows)) {
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Specialty', 'City', 'Phone'],
                collect($rows)->take(30)->map(fn ($r) => [
                    $r['provider_id'] ?? '',
                    Str::limit($r['provider_name_ar'] ?? '', 34),
                    Str::limit($r['provider_specialty'] ?? '', 22),
                    $r['city'] ?? '',
                    $r['phone'] ?? '',
                ])->all()
            );
            $this->info('Dry run — '.count($rows).' provider(s) would be imported.');

            return self::SUCCESS;
        }

        $summary = $service->import($rows);
        $this->info("Imported: {$summary['created']} created, {$summary['updated']} updated, {$summary['skipped']} skipped (of {$summary['total']}).");

        return self::SUCCESS;
    }
}

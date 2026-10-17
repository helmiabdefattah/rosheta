<?php

namespace App\Console\Commands;

use App\Support\RecurringExpenseGenerator;
use Illuminate\Console\Command;

/**
 * Create expenses from recurring-expense templates that have fallen due.
 *
 * Runs daily on the schedule (see routes/console.php) and can be invoked by
 * hand:
 *
 *   php artisan expenses:generate-recurring
 *   php artisan expenses:generate-recurring --clinic=12
 */
class GenerateRecurringExpenses extends Command
{
    protected $signature = 'expenses:generate-recurring
        {--clinic= : Only generate for a single clinic id}';

    protected $description = 'Generate expenses from due recurring-expense templates';

    public function handle(RecurringExpenseGenerator $generator): int
    {
        $clinicId = $this->option('clinic') ? (int) $this->option('clinic') : null;

        $count = $generator->generateDue($clinicId);

        $this->info("Generated {$count} recurring expense(s).");

        return self::SUCCESS;
    }
}

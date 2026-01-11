<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Notifications\TestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTestNotificationToClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-clients 
                            {--title= : Custom notification title}
                            {--message= : Custom notification message}
                            {--limit= : Limit number of clients to send to}
                            {--with-tokens-only : Only send to clients with FCM tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to all clients (or a subset)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->option('title');
        $message = $this->option('message');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $withTokensOnly = $this->option('with-tokens-only');

        $this->info('Preparing to send test notifications...');
        $this->newLine();

        // Build query
        $query = Client::query();

        if ($withTokensOnly) {
            $query->where(function ($q) {
                $q->whereNotNull('fcm_token_web')
                  ->orWhereNotNull('fcm_token_mobile');
            });
        }

        $totalClients = $query->count();

        if ($totalClients === 0) {
            $this->warn('No clients found matching the criteria.');
            return Command::FAILURE;
        }

        if ($limit) {
            $query->limit($limit);
            $this->info("Found {$totalClients} clients. Sending to first {$limit}...");
        } else {
            $this->info("Found {$totalClients} clients. Sending to all...");
        }

        $this->newLine();

        $clients = $query->get();
        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;
        $noTokenCount = 0;

        foreach ($clients as $client) {
            try {
                // Check if client has any FCM tokens
                if (!$client->fcm_token_web && !$client->fcm_token_mobile) {
                    $noTokenCount++;
                    $bar->advance();
                    continue;
                }

                // Send notification
                $client->notify(new TestNotification($title, $message));
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                Log::error('Failed to send test notification to client', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info('Notification sending completed!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Failed', $failureCount],
                ['No FCM Tokens', $noTokenCount],
                ['Total Processed', $clients->count()],
            ]
        );

        if ($failureCount > 0) {
            $this->warn("{$failureCount} notifications failed to send. Check logs for details.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

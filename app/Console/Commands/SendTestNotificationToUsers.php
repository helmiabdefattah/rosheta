<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTestNotificationToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-users 
                            {--title= : Custom notification title}
                            {--message= : Custom notification message}
                            {--limit= : Limit number of users to send to}
                            {--with-tokens-only : Only send to users with FCM tokens}
                            {--type= : Filter by user type (admin, laboratory, pharmacy, nurse)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to all users (or a subset)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->option('title');
        $message = $this->option('message');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $withTokensOnly = $this->option('with-tokens-only');
        $type = $this->option('type');

        $this->info('Preparing to send test notifications...');
        $this->newLine();

        // Build query
        $query = User::query();

        // Filter by user type if specified
        if ($type) {
            switch (strtolower($type)) {
                case 'admin':
                    $query->where('is_admin', true);
                    break;
                case 'laboratory':
                    $query->whereNotNull('laboratory_id');
                    break;
                case 'pharmacy':
                    $query->whereNotNull('pharmacy_id');
                    break;
                case 'nurse':
                    $query->whereNotNull('nurse_id');
                    break;
                default:
                    $this->warn("Unknown user type: {$type}. Valid types: admin, laboratory, pharmacy, nurse");
                    return Command::FAILURE;
            }
        }

        if ($withTokensOnly) {
            $query->where(function ($q) {
                $q->whereNotNull('fcm_token_web')
                  ->orWhereNotNull('fcm_token_mobile');
            });
        }

        $totalUsers = $query->count();

        if ($totalUsers === 0) {
            $this->warn('No users found matching the criteria.');
            return Command::FAILURE;
        }

        if ($limit) {
            $query->limit($limit);
            $this->info("Found {$totalUsers} users. Sending to first {$limit}...");
        } else {
            $this->info("Found {$totalUsers} users. Sending to all...");
        }

        if ($type) {
            $this->info("Filtering by type: {$type}");
        }

        $this->newLine();

        $users = $query->get();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;
        $noTokenCount = 0;

        foreach ($users as $user) {
            try {
                // Check if user has any FCM tokens
                if (!$user->fcm_token_web && !$user->fcm_token_mobile) {
                    $noTokenCount++;
                    $bar->advance();
                    continue;
                }

                // Send notification
                $user->notify(new TestNotification($title, $message));
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                Log::error('Failed to send test notification to user', [
                    'user_id' => $user->id,
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
                ['Total Processed', $users->count()],
            ]
        );

        if ($failureCount > 0) {
            $this->warn("{$failureCount} notifications failed to send. Check logs for details.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

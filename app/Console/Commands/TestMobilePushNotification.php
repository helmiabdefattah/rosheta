<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class TestMobilePushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-mobile-push
                            {--staff : Use staff users (User model) instead of app clients (Client model)}
                            {--id= : Only include / target this client or user id (see --staff)}
                            {--send : Send a test push notification (requires mobile FCM token on the record)}
                            {--force : With --send, skip confirmation (non-interactive / CI)}
                            {--title= : Custom notification title}
                            {--message= : Custom notification body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List clients (or staff) with name & mobile, and optionally send a test FCM push to verify mobile notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $staff = (bool) $this->option('staff');
        $id = $this->option('id') !== null && $this->option('id') !== ''
            ? (int) $this->option('id')
            : null;

        if ($staff) {
            return $this->runForModel(User::query(), 'User', 'phone_number', $id);
        }

        return $this->runForModel(Client::query(), 'Client', 'phone_number', $id);
    }

    /**
     * @param  Builder<User>|Builder<Client>  $query
     */
    protected function runForModel(Builder $query, string $label, string $phoneColumn, ?int $id): int
    {
        if ($id !== null) {
            $query->where('id', $id);
        }

        $rows = $query->orderBy('id')->get(['id', 'name', $phoneColumn, 'fcm_token_mobile']);

        if ($rows->isEmpty()) {
            $this->warn("No {$label} records found" . ($id !== null ? " for id={$id}." : '.'));

            return Command::FAILURE;
        }

        $verbose = $this->output->isVerbose();

        $tableData = $rows->map(function ($record) use ($phoneColumn, $verbose) {
            $mobile = $record->{$phoneColumn} ?? '';

            $row = [
                $record->id,
                $record->name ?? '',
                $mobile,
                $record->fcm_token_mobile ? 'yes' : 'no',
            ];

            if ($verbose) {
                $row[] = $record->fcm_token_mobile
                    ? (substr((string) $record->fcm_token_mobile, 0, 14) . '…')
                    : '—';
            }

            return $row;
        })->all();

        $headers = ['ID', 'Name', 'Mobile', 'Has mobile FCM'];
        if ($verbose) {
            $headers[] = 'Token (prefix)';
        }

        $this->info($label . ' (name, mobile, mobile FCM)');
        $this->table($headers, $tableData);

        if (! $this->option('send')) {
            $this->newLine();
            $this->comment('List only. Run with --send to dispatch a test push to records that have a mobile FCM token.');

            return Command::SUCCESS;
        }

        $targets = $rows->filter(fn ($r) => ! empty($r->fcm_token_mobile));

        if ($targets->isEmpty()) {
            $this->error('No mobile FCM tokens on the selected rows; nothing to send.');

            return Command::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Send test notification to ' . $targets->count() . ' ' . $label . '(s) with a mobile FCM token?', true)) {
            $this->warn('Cancelled.');

            return Command::SUCCESS;
        }

        $title = $this->option('title');
        $message = $this->option('message');
        $title = $title !== null && $title !== '' ? $title : null;
        $message = $message !== null && $message !== '' ? $message : null;

        $ok = 0;
        $fail = 0;

        foreach ($targets as $record) {
            try {
                $record->notify(new TestNotification($title, $message));
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                Log::error('test-mobile-push: notify failed', [
                    'model' => $label,
                    'id' => $record->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Sent: {$ok}, failed: {$fail}.");

        return $fail > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}

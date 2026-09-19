<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A visitor's demo run. Pinned to the production connection so it survives
 * the request-scoped switch to the demo database and outlives the tenant it
 * describes — it is the funnel record the marketing side reads.
 */
class DemoSession extends Model
{
    use HasUuids;

    /**
     * Never follows the demo tenant switch. Resolves to the funnel-records
     * connection (default 'mysql'); the main system can point this at a separate
     * demo deployment's DB via DEMO_RECORDS_CONNECTION to read demo-user reports.
     */
    public function getConnectionName()
    {
        return config('demo.records_connection', 'mysql');
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'ended_at' => 'datetime',
        'purged_at' => 'datetime',
        'steps_completed' => 'array',
        'activity_summary' => 'array',
        'baseline_counts' => 'array',
        'analyzed_at' => 'datetime',
    ];

    /** What they said on the way out, if they said anything. */
    public function survey(): HasOne
    {
        return $this->hasOne(DemoSurvey::class, 'demo_session_id');
    }

    /** Everything they did, in order. */
    public function events(): HasMany
    {
        return $this->hasMany(DemoActivityEvent::class, 'demo_session_id')->orderBy('occurred_at');
    }

    /**
     * The stored analysis, or an empty array for a run that ended before this
     * was ever recorded.
     */
    public function analysis(): array
    {
        return (array) ($this->activity_summary ?? []);
    }

    /** A run nobody has closed yet, tenant still standing. */
    public function isRunning(): bool
    {
        return $this->ended_at === null && $this->purged_at === null;
    }

    /** How long the run lasted, in seconds, however it ended. */
    public function durationSeconds(): ?int
    {
        $end = $this->ended_at ?? ($this->isRunning() ? now() : null);

        // Carbon returns a float here; the column and everything reading it
        // are whole seconds.
        return $this->started_at && $end ? (int) max(0, round($this->started_at->diffInSeconds($end))) : null;
    }

    /** Still running: not ended, not expired, not idle. */
    public function isActive(): bool
    {
        return $this->ended_at === null && ! $this->isExpired() && ! $this->isIdle();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isIdle(): bool
    {
        $timeout = (int) config('demo.idle_timeout_minutes');

        if ($timeout <= 0 || $this->last_activity_at === null) {
            return false;
        }

        return $this->last_activity_at->addMinutes($timeout)->isPast();
    }

    /** Why the session should end right now, or null if it should not. */
    public function expiryReason(): ?string
    {
        if ($this->ended_at !== null) {
            return $this->end_reason ?? 'user_ended';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isIdle()) {
            return 'idle';
        }

        return null;
    }

    /** Seconds left before the hard expiry, for the countdown in the demo bar. */
    public function secondsRemaining(): int
    {
        if ($this->expires_at === null) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }

    /** Sessions whose tenant should be hard-deleted. */
    public function scopePurgeable($query)
    {
        $idle = now()->subMinutes((int) config('demo.idle_timeout_minutes'));

        return $query->whereNotNull('doctor_id')
            ->where(function ($q) use ($idle) {
                $q->whereNotNull('ended_at')
                    ->orWhere('expires_at', '<', now())
                    ->orWhere('last_activity_at', '<', $idle);
            });
    }
}

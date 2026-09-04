<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A visitor's demo run. Pinned to the production connection so it survives
 * the request-scoped switch to the demo database and outlives the tenant it
 * describes — it is the funnel record the marketing side reads.
 */
class DemoSession extends Model
{
    use HasUuids;

    /** Never follows the demo connection switch. */
    protected $connection = 'mysql';

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
    ];

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

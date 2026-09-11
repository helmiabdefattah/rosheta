<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing a demo visitor did.
 *
 * Pinned to production like DemoSession and DemoSurvey: it is written by a
 * demo request about a tenant that is hard-deleted at the end of the run, so
 * it has to refuse the connection switch to be worth anything at all.
 *
 * No updated_at — an event is a fact about a moment, never edited.
 */
class DemoActivityEvent extends Model
{
    /** Never follows the demo connection switch. */
    protected $connection = 'mysql';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DemoSession::class, 'demo_session_id');
    }

    public function isAction(): bool
    {
        return $this->kind === 'action';
    }

    /** The sentence to show, in the admin's current language. */
    public function sentence(bool $arabic = true): string
    {
        $label = $arabic ? $this->label : ($this->label_en ?: $this->label);

        return (string) ($label ?: $this->action);
    }
}

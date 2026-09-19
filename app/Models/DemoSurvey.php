<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The three questions asked when a demo ends: was it useful, what would you
 * add, what would you drop.
 *
 * Pinned to production like DemoSession — it is written by a demo request
 * (which is pointed at the demo database) about a tenant that is deleted
 * moments later, so it has to refuse the connection switch to be worth
 * anything at all.
 */
class DemoSurvey extends Model
{
    /**
     * Never follows the demo tenant switch. Resolves to the funnel-records
     * connection (default 'mysql'); the main system can point this at a separate
     * demo deployment's DB via DEMO_RECORDS_CONNECTION to read demo-user reports.
     */
    public function getConnectionName()
    {
        return config('demo.records_connection', 'mysql');
    }

    protected $guarded = [];

    protected $casts = [
        'was_useful' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DemoSession::class, 'demo_session_id');
    }
}

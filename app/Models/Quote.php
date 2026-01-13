<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'client_id',
        'quote',
        'reply',
    ];

    /**
     * Get the parent quotable model (Laboratory, Pharmacy, etc.).
     */
    public function quotable(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Get the client who created the quote.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

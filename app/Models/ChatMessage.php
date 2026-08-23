<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line in a doctor↔patient conversation. The writer is identified by
 * `sender` ('doctor' or 'client'); who those two people are comes from the
 * parent conversation.
 */
class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public const SENDER_DOCTOR = 'doctor';
    public const SENDER_CLIENT = 'client';

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** Messages written by the other side, still unread by `$side`. */
    public function scopeUnreadFor(Builder $query, string $side): Builder
    {
        return $query->where('sender', Conversation::otherSide($side))->whereNull('read_at');
    }

    public function isFrom(string $side): bool
    {
        return $this->sender === $side;
    }
}

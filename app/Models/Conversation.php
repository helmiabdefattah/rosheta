<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A running chat thread between one doctor and one patient (Client). There is
 * at most one per pair — see the unique index on (doctor_id, client_id) — so a
 * patient's whole correspondence with a doctor lives in a single thread.
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'client_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /** The two sides of a thread, as stored in chat_messages.sender. */
    public const SIDE_DOCTOR = ChatMessage::SENDER_DOCTOR;
    public const SIDE_CLIENT = ChatMessage::SENDER_CLIENT;

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** The patient. Patients are `clients` in this codebase. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    /** Given one side of a thread, the other one. */
    public static function otherSide(string $side): string
    {
        return $side === self::SIDE_DOCTOR ? self::SIDE_CLIENT : self::SIDE_DOCTOR;
    }

    /** The thread for this pair, created on first use. */
    public static function between(Doctor $doctor, Client $client): self
    {
        return static::firstOrCreate([
            'doctor_id' => $doctor->id,
            'client_id' => $client->id,
        ]);
    }

    /**
     * Append a message and bump the thread's sort key. Returns the new message.
     */
    public function post(string $side, string $body): ChatMessage
    {
        $message = $this->messages()->create([
            'sender' => $side,
            'body' => $body,
        ]);

        $this->forceFill(['last_message_at' => $message->created_at])->save();

        return $message;
    }

    /** Mark everything the other side wrote as read by `$side`. */
    public function markReadBy(string $side): int
    {
        return $this->messages()->unreadFor($side)->update(['read_at' => now()]);
    }

    /** How many messages `$side` has not opened yet. */
    public function unreadCountFor(string $side): int
    {
        return $this->messages()->unreadFor($side)->count();
    }
}

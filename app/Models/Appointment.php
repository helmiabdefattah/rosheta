<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'user_id',
        'client_id',
        'appointment_date',
        'appointment_time',
        // Clinic (design) workflow fields.
        'scheduled_at',
        'queue_number',
        'source',
        'reason',
        'type',
        'price',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'scheduled_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public const TYPES = [
        'medical_examination' => 'medical_examination',
        'follow_up' => 'follow_up',
        'examination' => 'examination',
        'consultation' => 'consultation',
    ];

    public const STATUSES = [
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'completed' => 'completed',
        'missed' => 'missed',
        'cancelled' => 'cancelled',
        'scheduled' => 'scheduled',
        'under_examination' => 'under_examination',
        'escaped' => 'escaped',
    ];

    /**
     * Keep the canonical clinic timestamp (scheduled_at) and rosheta's
     * appointment_date / appointment_time columns in sync, whichever side is
     * written. This lets both systems share one row: the clinic queue reads
     * scheduled_at, rosheta booking reads appointment_date/time.
     */
    protected static function booted(): void
    {
        static::saving(function (self $a) {
            if ($a->isDirty('scheduled_at') && $a->scheduled_at) {
                $sa = $a->scheduled_at instanceof Carbon ? $a->scheduled_at : Carbon::parse($a->scheduled_at);
                $a->appointment_date = $sa->toDateString();
                $a->appointment_time = $sa->format('H:i:s');
            } elseif (($a->isDirty('appointment_date') || $a->isDirty('appointment_time') || ! $a->scheduled_at) && $a->appointment_date) {
                $date = $a->appointment_date instanceof Carbon ? $a->appointment_date->toDateString() : (string) $a->appointment_date;
                $timeRaw = $a->appointment_time;
                $time = $timeRaw instanceof Carbon ? $timeRaw->format('H:i:s') : ($timeRaw ?: '00:00:00');
                $a->scheduled_at = Carbon::parse($date)->setTimeFromTimeString($time);
            }
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ---- Clinic (design) system relationships ----

    public function diagnosis(): HasOne
    {
        // One diagnosis per appointment (kept in sync via updateOrCreate).
        return $this->hasOne(Diagnosis::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function medicalRequests(): HasMany
    {
        return $this->hasMany(MedicalRequest::class);
    }

    /** Payments taken from the patient for this visit. */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /** Chargeable extras added during the examination. */
    public function items(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }

    /**
     * The visit fee itself. Falls back to the clinic's current price for the
     * visit type, so appointments created before prices were configured (price
     * left null) still show something to collect.
     */
    public function visitPrice(): float
    {
        return (float) ($this->price ?? $this->clinic?->priceFor($this->type) ?? 0);
    }

    /** Total of the chargeable extras added during the examination. */
    public function itemsTotal(): float
    {
        return round($this->items->sum(fn (AppointmentItem $i) => $i->total()), 2);
    }

    /** Everything the patient owes for this visit: the fee plus any extras. */
    public function dueAmount(): float
    {
        return round($this->visitPrice() + $this->itemsTotal(), 2);
    }

    /** Total already taken from the patient. */
    public function collectedAmount(): float
    {
        return (float) $this->collections->sum('amount');
    }

    /**
     * Signed difference between what's owed and what's been taken. Positive =
     * the patient still owes; negative = they've overpaid and are due a refund
     * (which happens when the fee is discounted after they've already paid).
     */
    public function balance(): float
    {
        return round($this->dueAmount() - $this->collectedAmount(), 2);
    }

    /** What's still owed; never negative — see refundDue() for the other side. */
    public function remainingAmount(): float
    {
        return max(0, $this->balance());
    }

    /** Money owed back to the patient, after a discount or a cancelled charge. */
    public function refundDue(): float
    {
        return max(0, -$this->balance());
    }

    public function isFullyCollected(): bool
    {
        return $this->dueAmount() > 0 && $this->remainingAmount() <= 0;
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'appointment_id');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Human label for the visit type. Pass $locale to force a language — the
     * ticket printer does, so tickets print in the clinic's configured language
     * rather than the locale of whoever happened to trigger the print.
     */
    public function typeLabel(?string $locale = null): string
    {
        return match ($this->type) {
            'examination', 'medical_examination' => __('app.types.examination', [], $locale),
            default => __('app.types.consultation', [], $locale),
        };
    }

    public function statusLabel(): string
    {
        return __('app.statuses.'.$this->status);
    }
}

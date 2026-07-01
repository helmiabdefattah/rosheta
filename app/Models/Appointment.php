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

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'appointment_id');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'examination', 'medical_examination' => __('app.types.examination'),
            default => __('app.types.consultation'),
        };
    }

    public function statusLabel(): string
    {
        return __('app.statuses.'.$this->status);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Doctor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'name',
        'slug',
        'brief',
        'assistant_limit',
        'chat_enabled',
        'chat_window_days',
    ];

    protected $casts = [
        'assistant_limit' => 'integer',
        'chat_enabled' => 'boolean',
        'chat_window_days' => 'integer',
    ];

    /** Assistant accounts a doctor may create per clinic before an admin raises it. */
    public const DEFAULT_ASSISTANT_LIMIT = 2;

    /** Days a patient may keep writing after their last visit, when unset. */
    public const DEFAULT_CHAT_WINDOW_DAYS = 30;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    /** Session key holding the clinic the user picked in the practice workspace. */
    public const ACTIVE_CLINIC_SESSION_KEY = 'practice.clinic_id';

    /**
     * The clinic the practice workspace is currently working in: whichever the
     * user switched to, falling back to their first. The session id is always
     * re-checked against this doctor's own clinics, so a stale or tampered id
     * simply falls back instead of leaking another doctor's clinic.
     *
     * An assistant hired into a specific clinic is pinned to it — only the
     * doctor switches between clinics.
     */
    public function activeClinic(): ?Clinic
    {
        $user = auth()->user();
        if ($user && $user->doctor_id === $this->id && $user->clinic_id) {
            $pinned = $this->clinics()->whereKey($user->clinic_id)->first();
            if ($pinned) {
                return $pinned;
            }
        }

        $selected = session(self::ACTIVE_CLINIC_SESSION_KEY);

        return ($selected ? $this->clinics()->whereKey($selected)->first() : null)
            ?? $this->clinics()->first();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** The doctor's price list of chargeable extras, shared across their clinics. */
    public function billableItems(): HasMany
    {
        return $this->hasMany(BillableItem::class);
    }

    /** Reusable prescription templates saved by this doctor. */
    public function medicalPlans(): HasMany
    {
        return $this->hasMany(MedicalPlan::class);
    }

    /** Custom examination fields defined by this doctor. */
    public function examinationFields(): HasMany
    {
        return $this->hasMany(ExaminationField::class);
    }

    /** Users who are this doctor's assistants (users.doctor_id → doctors.id). */
    public function assistants(): HasMany
    {
        return $this->hasMany(User::class, 'doctor_id');
    }

    /** How many assistants this doctor may have at each of their clinics. */
    public function assistantLimit(): int
    {
        return (int) ($this->assistant_limit ?: self::DEFAULT_ASSISTANT_LIMIT);
    }

    /** Assistants already hired into a given clinic. */
    public function assistantsAtClinic(int $clinicId): HasMany
    {
        return $this->assistants()->where('clinic_id', $clinicId);
    }

    /** Whether this doctor still has an assistant slot free at the given clinic. */
    public function canAddAssistantAt(int $clinicId): bool
    {
        return $this->assistantsAtClinic($clinicId)->count() < $this->assistantLimit();
    }

    /** True when the login behind this doctor profile is active (admin switch). */
    public function accountIsActive(): bool
    {
        return (bool) $this->user?->is_active;
    }

    /** Diagnoses recorded by this doctor in the clinic system. */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    /** Prescriptions issued by this doctor in the clinic system. */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /** Per-doctor working hours at a clinic (when clinic has multiple doctors). */
    public function clinicDoctorWorkingHours(): HasMany
    {
        return $this->hasMany(ClinicDoctorWorkingHour::class, 'doctor_id');
    }

    /** Chat threads this doctor has with their patients. */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** How many days after a visit this doctor keeps the chat open. */
    public function chatWindowDays(): int
    {
        return max(1, (int) ($this->chat_window_days ?: self::DEFAULT_CHAT_WINDOW_DAYS));
    }

    /**
     * When the chat window for a patient closes: their most recent *past*
     * appointment with this doctor plus the configured number of days.
     *
     * Cancelled visits do not count — a patient who called off their booking
     * never actually saw the doctor. Returns null when the doctor has chat off
     * or the patient has no qualifying visit, i.e. the window never opened.
     */
    public function chatWindowEndsFor(Client $client): ?\Carbon\Carbon
    {
        if (! $this->chat_enabled) {
            return null;
        }

        $lastVisit = $this->appointments()
            ->where('client_id', $client->id)
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '<=', now())
            ->max('scheduled_at');

        return $lastVisit ? \Carbon\Carbon::parse($lastVisit)->addDays($this->chatWindowDays()) : null;
    }

    /** Whether this patient may still write to this doctor right now. */
    public function chatOpenFor(Client $client): bool
    {
        return $this->chatWindowEndsFor($client)?->isFuture() ?? false;
    }

    /** Days when this doctor is off (at a specific clinic or all clinics if clinic_id null). */
    public function offDates(): HasMany
    {
        return $this->hasMany(DoctorOffDate::class, 'doctor_id');
    }

    /** Check if this doctor is off on a given date (at clinic or globally). */
    public function isOffOnDate(string $date, ?int $clinicId = null): bool
    {
        $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');
        return $this->offDates()
            ->where('off_date', $dateStr)
            ->where(function ($q) use ($clinicId) {
                $q->whereNull('clinic_id');
                if ($clinicId !== null) {
                    $q->orWhere('clinic_id', $clinicId);
                }
            })
            ->exists();
    }
}

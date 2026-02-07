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
    ];

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

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Per-doctor working hours at a clinic (when clinic has multiple doctors). */
    public function clinicDoctorWorkingHours(): HasMany
    {
        return $this->hasMany(ClinicDoctorWorkingHour::class, 'doctor_id');
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

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'registration_license_number',
        'password',
        'pharmacy_id',
        'laboratory_id',
        'nurse_id',
        'doctor_id',
        'charitable_organization_id',
        'fcm_token_web',
        'fcm_token_mobile',
        'notification_sound',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The pharmacy this user belongs to.
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The laboratory this user belongs to.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * The nurse profile associated with this user.
     */
    public function nurse(): HasOne
    {
        return $this->hasOne(Nurse::class, 'user_id');
    }

    /**
     * Charitable organization this user represents (if any).
     */
    public function charitableOrganization(): BelongsTo
    {
        return $this->belongsTo(CharitableOrganization::class);
    }

    /**
     * The doctor profile associated with this user (if any).
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    /**
     * Clinic this user manages (staff portal), when set on clinics.user_id.
     */
    public function managedClinic(): HasOne
    {
        return $this->hasOne(Clinic::class, 'user_id');
    }

    /**
     * When this user is a doctor's assistant, the doctor they work for.
     * (users.doctor_id → doctors.id)
     */
    public function assistantDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /** True when this user owns a doctor profile. */
    public function isDoctor(): bool
    {
        return $this->doctor()->exists();
    }

    /** True when this user is a doctor's assistant. */
    public function isAssistant(): bool
    {
        return $this->doctor_id !== null;
    }

    /**
     * The Doctor this user operates as in the clinic system: their own doctor
     * profile, or the doctor they assist. Null for anyone else.
     */
    public function clinicDoctor(): ?Doctor
    {
        return $this->doctor ?: $this->assistantDoctor;
    }

    /**
     * Appointments (as patient).
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'test@example.com';
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->laboratory_id === null
            && $this->pharmacy_id === null
            && $this->charitable_organization_id === null
            && !$this->nurse()->exists()
            && !$this->doctor()->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('service_registration_documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ]);
    }
}

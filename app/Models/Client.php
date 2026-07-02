<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'phone_number', 'email', 'password', 'avatar', 'governorate_id', 'city_id',
        'insurance_company_id', 'fcm_token_web', 'fcm_token_mobile', 'notification_sound',
        // Clinic (design) system patient profile fields.
        'gender', 'dob', 'national_id', 'blood_type', 'address',
        'allergies', 'chronic_diseases', 'medical_history',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'allergies' => 'array',
            'chronic_diseases' => 'array',
        ];
    }

    /** Age in whole years, computed from date of birth. */
    public function getAgeAttribute(): ?int
    {
        return $this->dob ? \Carbon\Carbon::parse($this->dob)->age : null;
    }

    /**
     * Normalize a value into a clean list of strings (allergies / chronic
     * diseases). Accepts an array or a string split on newlines/commas; trims,
     * drops blanks, de-duplicates. Returns null when empty.
     */
    public static function toList($value): ?array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n|,/', (string) $value);

        $list = collect($items)
            ->map(fn ($i) => trim((string) $i))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $list ?: null;
    }

    /** Clinic diagnoses recorded for this patient. */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    /** Clinic prescriptions issued to this patient. */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /** Patient files (polymorphic attachments). */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function addresses()
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function requests()
    {
        return $this->hasMany(ClientRequest::class);
    }
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $client) {
            // Award welcome points (once)
            \App\Models\BonusPoint::awardUnique(
                clientId: (int) $client->id,
                sourceType: 'welcome',
                sourceId: 0,
                points: 50
            );
        });
    }
}

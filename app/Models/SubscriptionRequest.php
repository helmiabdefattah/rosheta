<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A "subscribe" submission from the public pricing page — the raw material the
 * team uses to create the doctor / clinic / assistant accounts by hand.
 *
 * Passwords are encrypted at rest via the casts below, so a database dump never
 * exposes them; they are decrypted only when an admin opens the request.
 */
class SubscriptionRequest extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_ACTIVATED = 'activated';

    protected $fillable = [
        'plan', 'billing', 'with_profile_site', 'profile_subdomain',
        'contact_name', 'contact_phone', 'contact_email',
        'doctor_name', 'doctor_specialty', 'doctor_username', 'doctor_phone', 'doctor_email', 'doctor_password',
        'clinic_name', 'clinic_address', 'clinic_city', 'clinic_phone',
        'assistants', 'notes', 'status', 'reviewed_at', 'locale', 'ip',
    ];

    protected $casts = [
        'with_profile_site' => 'boolean',
        'doctor_password' => 'encrypted',
        'assistants' => 'encrypted:array',
        'reviewed_at' => 'datetime',
    ];

    /** Human label for the chosen plan. */
    public function planLabel(): string
    {
        return match ($this->plan) {
            'starter' => 'Starter',
            'equipped' => 'Equipped',
            'multi' => 'Multi-Clinic',
            default => $this->plan ?: '—',
        };
    }

    /** How many assistant accounts were requested. */
    public function assistantsCount(): int
    {
        return is_array($this->assistants) ? count($this->assistants) : 0;
    }
}

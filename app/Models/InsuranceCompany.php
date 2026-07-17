<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the clients for this insurance company.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function appointmentInsurances(): HasMany
    {
        return $this->hasMany(AppointmentInsurance::class);
    }

    public function insuranceCollections(): HasMany
    {
        return $this->hasMany(InsuranceCollection::class);
    }

    /** Localized display name: the Arabic name when available under the ar locale. */
    public function displayName(): string
    {
        if (app()->getLocale() === 'ar' && filled($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name ?: ($this->name_ar ?? '');
    }
}

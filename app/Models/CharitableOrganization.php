<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharitableOrganization extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (CharitableOrganization $organization) {
            $organization->syncLinkedUsersActiveState();
        });
    }

    /**
     * When the organization is activated in admin, allow linked owner accounts to log in.
     */
    public function syncLinkedUsersActiveState(): void
    {
        $active = (bool) $this->is_active;
        $ids = collect([$this->user_id])
            ->merge(User::where('charitable_organization_id', $this->id)->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        User::whereIn('id', $ids)->update(['is_active' => $active]);
    }

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'governorate_id',
        'city_id',
        'area_id',
        'phone_numbers',
        'services',
        'is_active',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'services' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Governorate for this organization.
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get the city that owns the charitable organization.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the area that owns the charitable organization.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}

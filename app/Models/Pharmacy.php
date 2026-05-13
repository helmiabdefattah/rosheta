<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pharmacy extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (Pharmacy $pharmacy) {
            $pharmacy->syncLinkedUsersActiveState();
        });
    }

    /**
     * Keep pharmacy owner login (users.is_active) aligned with pharmacy visibility flag.
     */
    public function syncLinkedUsersActiveState(): void
    {
        $active = (bool) $this->is_active;
        $ids = collect([$this->user_id])
            ->merge(User::where('pharmacy_id', $this->id)->pluck('id'))
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
        'area_id',
        'name',
        'phone',
        'email',
        'address',
        'location',
        'lat',
        'lng',
        'license_number',
        'pharmacist_name',
        'pharmacist_license',
        'opening_time',
        'closing_time',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the pharmacy.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the area that the pharmacy belongs to.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

	public function reviews()
	{
		return $this->morphMany(Review::class, 'reviewable');
	}

	public function quotes()
	{
		return $this->morphMany(Quote::class, 'model');
	}
}

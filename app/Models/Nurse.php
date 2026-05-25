<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Add this

class Nurse extends Model
{
    use HasFactory;

    protected $table = 'nurses';

    protected static function booted(): void
    {
        static::saved(function (Nurse $nurse) {
            $nurse->syncLinkedUsersActiveState();
        });
    }

    /**
     * Keep nurse account login (users.is_active) aligned with nurse status.
     */
    public function syncLinkedUsersActiveState(): void
    {
        $active = $this->status === 'active';
        $ids = collect([$this->user_id])
            ->merge(User::where('nurse_id', $this->id)->pluck('id'))
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
        'gender',
        'date_of_birth',
        'address',
        'status',
        'area_ids',
        'qualification',
        'education_place',
        'graduation_year',
        'years_of_experience',
        'current_workplace',
        'certifications',
        'skills',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'area_ids' => 'array',
        'certifications' => 'array',
        'skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Add these relationships
    public function offers(): HasMany
    {
        return $this->hasMany(NurseOffer::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(NurseVisit::class);
    }

    // Optional: Add a scope for active nurses
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function supportTickets()
    {
        return $this->morphMany(SupportTicket::class, 'ticketable');
    }
}

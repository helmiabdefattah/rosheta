<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NurseOffer extends Model
{
    use HasFactory;

    protected $table = 'nurse_offers';

    protected $fillable = [
        'home_nurse_request_id',
        'nurse_id',
        'price',
        'total_price',
        'visit_price',
        'visits_count',
        'visit_period',
        'notes',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'visit_price' => 'decimal:2',
        'visits_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function request(): BelongsTo
    {
        return $this->belongsTo(HomeNurseRequest::class, 'home_nurse_request_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(NurseVisit::class, 'nurse_offer_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Schedule visits based on the related HomeNurseRequest
     */
    public function scheduleVisits(): void
    {
        $request = $this->request;
        if (!$request) {
            return;
        }

        $count = (int) $this->visits_count;
        if ($count <= 0) {
            return;
        }

        $start = Carbon::parse($request->visit_start_date);
        $current = $start->copy();

        for ($i = 0; $i < $count; $i++) {
            $this->visits()->create([
                'home_nurse_request_id' => $this->home_nurse_request_id,
                'nurse_id' => $this->nurse_id,
                'visit_datetime' => $current->copy(),
                'status' => 'scheduled',
            ]);

            switch ($request->visit_frequency) {
                case 'daily':
                    $current->addDay();
                    break;
                case 'every_two_days':
                    $current->addDays(2);
                    break;
                case 'once_weekly':
                    $current->addWeek();
                    break;
                case 'twice_weekly':
                    $current->addDays(($i % 2) ? 4 : 3);
                    break;
                default:
                    $current->addWeek();
            }
        }

    }
}

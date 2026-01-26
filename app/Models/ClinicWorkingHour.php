<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicWorkingHour extends Model
{
    use HasFactory;

    protected $table = 'clinic_working_hours';

    protected $fillable = [
        'clinic_id',
        'day',
        'from',
        'to',
        'is_closed',
    ];

    protected $casts = [
        'from' => 'datetime:H:i',
        'to' => 'datetime:H:i',
        'is_closed' => 'boolean',
    ];

    public const DAYS = [
        'saturday' => 6,
        'sunday' => 0,
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}

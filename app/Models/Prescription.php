<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'appointment_id', 'client_id', 'doctor_id', 'diagnosis_id', 'notes',
        'sick_leave_days', 'self_printed_at',
    ];

    protected $casts = [
        'sick_leave_days' => 'integer',
        'self_printed_at' => 'datetime',
    ];

    /** A patient may print their own copy from the waiting-room screen once. */
    public function wasSelfPrinted(): bool
    {
        return $this->self_printed_at !== null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }
}

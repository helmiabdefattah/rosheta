<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationFieldValue extends Model
{
    protected $fillable = [
        'appointment_id',
        'examination_field_id',
        'value',
        'attachment_id',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(ExaminationField::class, 'examination_field_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}

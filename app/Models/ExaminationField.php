<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A doctor-defined custom field shown on the examination screen.
 */
class ExaminationField extends Model
{
    public const TYPES = ['text', 'select', 'number', 'percentage', 'file'];

    protected $fillable = [
        'doctor_id',
        'label',
        'type',
        'options',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ExaminationFieldValue::class);
    }

    /** Choices for a "select" field, parsed from the comma-separated options. */
    public function optionsArray(): array
    {
        if (blank($this->options)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->options)), 'strlen'));
    }
}

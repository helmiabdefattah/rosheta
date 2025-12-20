<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryTestPrice extends Model
{
    protected $fillable = [
        'laboratory_id',
        'medical_test_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function medicalTest(): BelongsTo
    {
        return $this->belongsTo(MedicalTest::class);
    }
}

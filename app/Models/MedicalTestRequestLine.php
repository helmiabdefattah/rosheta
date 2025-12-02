<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalTestRequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_test_request_id',
        'medical_test_id',
    ];

    public function request()
    {
        return $this->belongsTo(MedicalTestRequest::class, 'medical_test_request_id');
    }

    public function medicalTest()
    {
        return $this->belongsTo(MedicalTest::class);
    }
}





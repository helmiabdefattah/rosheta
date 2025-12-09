<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequestTestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'medical_test_id',

    ];

    // Relationship with the parent request
    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    // Relationship with the medical test
    public function medicalTest()
    {
        return $this->belongsTo(MedicalTest::class, 'medical_test_id');
    }

    // Accessor to get test name in English
    public function getTestNameEnAttribute()
    {
        return $this->medicalTest ? $this->medicalTest->test_name_en : null;
    }

    // Accessor to get test name in Arabic
    public function getTestNameArAttribute()
    {
        return $this->medicalTest ? $this->medicalTest->test_name_ar : null;
    }

    // Accessor to get test description
    public function getTestDescriptionAttribute()
    {
        return $this->medicalTest ? $this->medicalTest->test_description : null;
    }

    // Accessor to get test conditions
    public function getConditionsAttribute()
    {
        return $this->medicalTest ? $this->medicalTest->conditions : null;
    }
}

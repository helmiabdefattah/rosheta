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

    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    public function medicalTest()
    {
        return $this->belongsTo(MedicalTest::class);
    }
}



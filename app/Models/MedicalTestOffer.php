<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalTestOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_test_id',
        'laboratory_id',
        'client_request_id',
        'price',
        'offer_price',
        'discount',
    ];

    public function medicalTest(): BelongsTo
    {
        return $this->belongsTo(MedicalTest::class, 'medical_test_id');
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

//    public function lines()
//    {
//        return $this->hasMany(MedicalTestOfferLine::class);
//    }

    public function clientRequest(): BelongsTo
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }
}





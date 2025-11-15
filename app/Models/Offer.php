<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'pharmacy_id',
        'user_id',
        'pharmacy_agent_id',
        'status',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    public function agent()
    {
        return $this->belongsTo(PharmacyAgent::class, 'pharmacy_agent_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lines()
    {
        return $this->hasMany(OfferLine::class);
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    public function requestLines()
    {
        return $this->hasMany(\App\Models\ClientRequestLine::class, 'client_request_id', 'client_request_id');
    }

}




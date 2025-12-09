<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'pharmacy_id',
        'user_id',
        'offer_id',
        'status',
        'payment_method',
        'payed',
        'total_price',
    ];

    protected $casts = [
        'payed' => 'boolean',
        'total_price' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }
}









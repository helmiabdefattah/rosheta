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
        'laboratory_id', // إضافة لحفظ المعمل للفحوصات الطبية

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

    protected static function booted(): void
    {
        static::updated(function (self $order) {
            // When an order becomes paid, award points equal to total_price (integer points)
            if ($order->isDirty('payed') && (bool)$order->payed === true) {
                $clientId = (int) ($order->request?->client_id ?? 0);
                $points = (int) round((float) $order->total_price);
                if ($clientId > 0 && $points > 0) {
                    \App\Models\BonusPoint::awardUnique(
                        clientId: $clientId,
                        sourceType: 'order',
                        sourceId: (int) $order->id,
                        points: $points
                    );
                }
            }
        });
    }

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




















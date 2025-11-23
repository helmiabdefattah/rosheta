<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'medicine_id',
        'quantity',
        'unit',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}









<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PharmacyAgent extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'phone',
        'email',
        'active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}




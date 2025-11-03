<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_address_id',
        'pregnant',
        'diabetic',
        'heart_patient',
        'high_blood_pressure',
        'note',
        'status',
    ];

    protected $casts = [
        'pregnant' => 'boolean',
        'diabetic' => 'boolean',
        'heart_patient' => 'boolean',
        'high_blood_pressure' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function address()
    {
        return $this->belongsTo(ClientAddress::class, 'client_address_id');
    }

    public function lines()
    {
        return $this->hasMany(ClientRequestLine::class);
    }
    
}




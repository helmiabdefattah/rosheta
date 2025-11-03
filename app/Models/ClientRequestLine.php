<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'medicine_id',
        'quantity',
        'unit',
    ];

    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}




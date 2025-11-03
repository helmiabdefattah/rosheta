<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientAddress extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'address', 'location','city_id','area_id'];

    protected $casts = [
        'location' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}


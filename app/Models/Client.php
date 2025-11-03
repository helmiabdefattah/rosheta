<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone_number'];

    public function addresses()
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function requests()
    {
        return $this->hasMany(ClientRequest::class);
    }
}

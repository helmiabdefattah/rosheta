<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalTestRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_address_id',
        'note',
        'status',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
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
        return $this->hasMany(MedicalTestRequestLine::class);
    }

    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];
        return array_map(function ($image) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url("medical-test-requests/{$image}");
        }, $images);
    }

    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $filenames = array_map(function ($path) {
                return basename($path);
            }, $value);
            $this->attributes['images'] = json_encode($filenames);
        } else {
            $this->attributes['images'] = json_encode($value);
        }
    }
}





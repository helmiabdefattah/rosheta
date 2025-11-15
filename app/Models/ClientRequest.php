<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'images', // ✅ added

    ];

    protected $casts = [
        'pregnant' => 'boolean',
        'diabetic' => 'boolean',
        'heart_patient' => 'boolean',
        'high_blood_pressure' => 'boolean',
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
        return $this->hasMany(ClientRequestLine::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'client_request_id');
    }
// In your ClientRequest model
    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];

        return array_map(function($image) {
            // This will generate: http://localhost/storage/requests/filename.png
            return Storage::disk('public')->url("requests/{$image}");
        }, $images);
    }
    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            // Store only filenames, not full paths
            $filenames = array_map(function($path) {
                return basename($path);
            }, $value);

            $this->attributes['images'] = json_encode($filenames);
        } else {
            $this->attributes['images'] = json_encode($value);
        }
    }
}




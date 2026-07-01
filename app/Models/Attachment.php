<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'uploaded_by',
        'appointment_id',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the parent attachable model (Offer, Client, etc.)
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /** The clinic staff user who uploaded this file (if any). */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Optional appointment this file was captured during. */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the full URL to the file
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }
}

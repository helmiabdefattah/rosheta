<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'pharmacy_id',
        'laboratory_id', // إضافة لحفظ المعمل للفحوصات الطبية
        'user_id',
        'status',
        'vendor_status',
        'total_price',
        'visit_price',
        'request_type', // 'medicine' أو 'test'
        'insurance_supported',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'visit_price' => 'decimal:2',
        'insurance_supported' => 'boolean',
    ];

    // Relationship with client request
    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    // Pharmacy relationship (for medicine offers)
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    // Laboratory relationship (for test offers)
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    // User who created the offer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Offer lines (for medicines)
    public function medicineLines()
    {
        return $this->hasMany(OfferLine::class);
    }

    // Offer lines (for medical tests)
    public function testLines()
    {
        return $this->hasMany(OfferLine::class);
    }

    // Dynamic relationship to get appropriate lines based on type
    public function lines()
    {
        return $this->request_type == 'test'
            ? $this->testLines()
            : $this->medicineLines();
    }

    // Helper method to get all lines with relationships
    public function getLinesWithDetails()
    {
        if ($this->request_type == 'test') {
            return $this->testLines()->with('medicalTest')->get();
        }
        return $this->medicineLines()->with('medicine')->get();
    }

    // Helper to get the provider (pharmacy or laboratory)
    public function getProviderAttribute()
    {
        if ($this->request_type == 'test') {
            return $this->laboratory;
        }
        return $this->pharmacy;
    }

    // Helper to get the provider name
    public function getProviderNameAttribute()
    {
        if ($this->request_type == 'test') {
            return $this->laboratory->name ?? 'N/A';
        }
        return $this->pharmacy->name ?? 'N/A';
    }

    // Helper to get the provider ID
    public function getProviderIdAttribute()
    {
        if ($this->request_type == 'test') {
            return $this->laboratory_id;
        }
        return $this->pharmacy_id;
    }

    // Scope for medicine offers
    public function scopeMedicineOffers($query)
    {
        return $query->where('request_type', 'medicine');
    }

    // Scope for test offers
    public function scopeTestOffers($query)
    {
        return $query->where('request_type', 'test');
    }

    // Status helpers
    public function isPending()
    {
        return $this->status == 'pending';
    }

    public function isAccepted()
    {
        return $this->status == 'accepted';
    }

    public function isRejected()
    {
        return $this->status == 'rejected';
    }

    // Type helpers
    public function isMedicineOffer()
    {
        return $this->request_type == 'medicine';
    }

    public function isTestOffer()
    {
        return $this->request_type == 'test';
    }

    // Get request type label
    public function getTypeLabelAttribute()
    {
        return $this->request_type == 'test' ? 'Medical Test' : 'Medicine';
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
            'accepted' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    // Get type badge class
    public function getTypeBadgeClassAttribute()
    {
        return $this->request_type == 'test'
            ? 'badge bg-info'
            : 'badge bg-primary';
    }

    // Get appropriate provider ID based on type
    public function getProviderId()
    {
        return $this->request_type == 'test'
            ? $this->laboratory_id
            : $this->pharmacy_id;
    }

    // Set appropriate provider based on type
    public function setProviderId($id)
    {
        if ($this->request_type == 'test') {
            $this->laboratory_id = $id;
        } else {
            $this->pharmacy_id = $id;
        }
    }

    // Attachments relationship (polymorphic)
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

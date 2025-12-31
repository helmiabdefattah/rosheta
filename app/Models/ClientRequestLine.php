<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'item_type', // 'medicine', 'test', or 'radiology'
        'medicine_id',
        'medical_test_id',
        'quantity',
        'unit',
        'notes',
    ];

    public function request()
    {
        return $this->belongsTo(ClientRequest::class, 'client_request_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function medicalTest()
    {
        return $this->belongsTo(MedicalTest::class);
    }

    // Dynamic relationship based on item_type
    public function item()
    {
        return match($this->item_type) {
            'medicine' => $this->medicine(),
            'test', 'radiology' => $this->medicalTest(), // Both use medical_test relationship
            default => $this->medicine(),
        };
    }

    // Accessor to get the actual item
    public function getItemAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medicalTest;
        }
        return $this->medicine;
    }

    // Accessor to get item name
    public function getItemNameAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medicalTest
                ? ($this->medicalTest->test_name_en . ' / ' . $this->medicalTest->test_name_ar)
                : 'N/A';
        }
        return $this->medicine->name ?? 'N/A';
    }

    // Accessor to get item name in English
    public function getItemNameEnAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medicalTest->test_name_en ?? 'N/A';
        }
        return $this->medicine->name ?? 'N/A';
    }

    // Accessor to get item name in Arabic
    public function getItemNameArAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medicalTest->test_name_ar ?? 'N/A';
        }
        return $this->medicine->name_ar ?? $this->medicine->name ?? 'N/A';
    }

    // Scope for medicine items
    public function scopeMedicineItems($query)
    {
        return $query->where('item_type', 'medicine');
    }

    // Scope for test items
    public function scopeTestItems($query)
    {
        return $query->where('item_type', 'test');
    }

    // Scope for radiology items
    public function scopeRadiologyItems($query)
    {
        return $query->where('item_type', 'radiology');
    }

    // Scope for medical test items (both test and radiology)
    public function scopeMedicalTestItems($query)
    {
        return $query->whereIn('item_type', ['test', 'radiology']);
    }

    // Get appropriate item ID based on type
    public function getItemIdAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medical_test_id;
        }
        return $this->medicine_id;
    }

    // Helper to check if item is a test
    public function getIsTestAttribute()
    {
        return $this->item_type === 'test';
    }

    // Helper to check if item is radiology
    public function getIsRadiologyAttribute()
    {
        return $this->item_type === 'radiology';
    }

    // Helper to check if item is medicine
    public function getIsMedicineAttribute()
    {
        return $this->item_type === 'medicine';
    }

    // Get the actual type from medical test if applicable
    public function getDetailedItemTypeAttribute()
    {
        if (in_array($this->item_type, ['test', 'radiology'])) {
            return $this->medicalTest->type ?? $this->item_type;
        }
        return $this->item_type;
    }

    // Cast quantity based on item type
    public function getDisplayQuantityAttribute()
    {
        if ($this->item_type == 'medicine') {
            return $this->quantity . ' ' . ($this->unit ?? 'box');
        }
        return '1 ' . $this->item_type; // For test and radiology items
    }

    // Get the appropriate label for the item type
    public function getTypeLabelAttribute()
    {
        return match($this->item_type) {
            'test' => __('Test'),
            'radiology' => __('Radiology'),
            'medicine' => __('Medicine'),
            default => ucfirst($this->item_type),
        };
    }
}

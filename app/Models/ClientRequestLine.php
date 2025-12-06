<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'item_type', // 'medicine' أو 'test'
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
        return $this->item_type == 'test'
            ? $this->medicalTest()
            : $this->medicine();
    }

    // Accessor to get the actual item
    public function getItemAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->medicalTest;
        }
        return $this->medicine;
    }

    // Accessor to get item name
    public function getItemNameAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->medicalTest ? ($this->medicalTest->test_name_en . ' / ' . $this->medicalTest->test_name_ar) : 'N/A';
        }
        return $this->medicine->name ?? 'N/A';
    }

    // Accessor to get item name in English
    public function getItemNameEnAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->medicalTest->test_name_en ?? 'N/A';
        }
        return $this->medicine->name ?? 'N/A';
    }

    // Accessor to get item name in Arabic
    public function getItemNameArAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->medicalTest->test_name_ar ?? 'N/A';
        }
        return $this->medicine->name_ar ?? 'N/A';
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

    // Get appropriate item ID based on type
    public function getItemIdAttribute()
    {
        return $this->item_type == 'test'
            ? $this->medical_test_id
            : $this->medicine_id;
    }
}

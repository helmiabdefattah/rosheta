<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'item_type', // 'medicine' أو 'test'
        'medicine_id',
        'medical_test_id',
        'quantity',
        'unit',
        'price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    // Relationship with offer
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    // Relationship with medicine (for medicine items)
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    // Relationship with medical test (for test items)
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

    // Accessor to get item name
    public function getItemNameAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->medicalTest ? ($this->medicalTest->test_name_en . ' / ' . $this->medicalTest->test_name_ar) : 'N/A';
        }
        return $this->medicine->name ?? 'N/A';
    }

    // Accessor to get total price
    public function getTotalPriceAttribute()
    {
        if ($this->item_type == 'test') {
            return $this->price; // For tests, price is already total
        }
        return $this->quantity * $this->price; // For medicines
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
}








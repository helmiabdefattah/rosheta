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
        'type', // 'medicine' or 'test'
        'images',
    ];

    protected $casts = [
        'pregnant' => 'boolean',
        'diabetic' => 'boolean',
        'heart_patient' => 'boolean',
        'high_blood_pressure' => 'boolean',
        'images' => 'array',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function address()
    {
        return $this->belongsTo(ClientAddress::class, 'client_address_id');
    }

    // العلاقة مع جميع الخطوط (استخدام جدول واحد)
    public function lines()
    {
        return $this->hasMany(ClientRequestLine::class);
    }

    // علاقة المساعدة للأدوية (للتوافق مع الكود الحالي)
    public function medicineLines()
    {
        return $this->lines()->medicineItems();
    }

    // علاقة المساعدة للفحوصات (للتوافق مع الكود الحالي)
    public function testLines()
    {
        return $this->hasMany(ClientRequestTestLine::class);
    }

    // Relationship with offers
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    // Get all lines with their relationships loaded
    public function getLinesWithDetails()
    {
        return $this->lines()->with(['medicine', 'medicalTest'])->get();
    }

    // Accessor for images
    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];

        return array_map(function($image) {
            return Storage::disk('public')->url("requests/{$image}");
        }, $images);
    }

    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $filenames = array_map(function($path) {
                return basename($path);
            }, $value);

            $this->attributes['images'] = json_encode($filenames);
        } else {
            $this->attributes['images'] = json_encode($value);
        }
    }

    // Helper methods
    public function isMedicineRequest()
    {
        return $this->type == 'medicine';
    }

    public function isTestRequest()
    {
        return $this->type == 'test';
    }

    // Get request type label
    public function getTypeLabelAttribute()
    {
        return $this->type == 'test' ? 'Medical Tests' : 'Medicines';
    }

    // Get request lines for API
    public function getRequestLinesForApi()
    {
        return $this->lines->map(function($line) {
            if ($line->item_type == 'test') {
                return [
                    'medical_test_id' => $line->medical_test_id,
                    'test_name_en' => $line->medicalTest->test_name_en ?? null,
                    'test_name_ar' => $line->medicalTest->test_name_ar ?? null,
                    'test_description' => $line->medicalTest->test_description ?? null,
                    'conditions' => $line->medicalTest->conditions ?? null,
                    'notes' => $line->notes,
                ];
            } else {
                return [
                    'medicine_id' => $line->medicine_id,
                    'medicine_name' => $line->medicine->name ?? null,
                    'dosage_form' => $line->medicine->dosage_form ?? null,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'old_price' => $line->medicine->price ?? null,
                    'notes' => $line->notes,
                ];
            }
        });
    }

    // Get lines based on request type (for backward compatibility)
    public function getLinesByType()
    {
        if ($this->type == 'test') {
            return $this->lines()->testItems()->with('medicalTest')->get();
        } else {
            return $this->lines()->medicineItems()->with('medicine')->get();
        }
    }
}

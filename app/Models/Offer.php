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
        'laboratory_id',
        'user_id',
        'status',
        'vendor_status',
        'total_price',
        'visit_price',
        'request_type', // 'medicine', 'test', or 'radiology'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'visit_price' => 'decimal:2',
    ];

    protected $appends = [
        'provider',
        'provider_name',
        'provider_id',
        'type_label',
        'status_badge_class',
        'type_badge_class',
    ];

    // Constants for request types
    const TYPE_MEDICINE = 'medicine';
    const TYPE_TEST = 'test';
    const TYPE_RADIOLOGY = 'radiology';

    // Constants for statuses
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DRAFT = 'draft';

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

    // Laboratory relationship (for test/radiology offers)
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

    // Offer lines (for medical tests - both test and radiology)
    public function testLines()
    {
        return $this->hasMany(OfferLine::class);
    }

    // Dynamic relationship to get appropriate lines based on type
    public function lines()
    {
        if ($this->request_type === self::TYPE_MEDICINE) {
            return $this->medicineLines();
        } else {
            return $this->testLines();
        }
    }

    // Get all offer lines with appropriate relationships
    public function getLinesWithDetails()
    {
        if ($this->request_type === self::TYPE_MEDICINE) {
            return $this->medicineLines()->with('medicine')->get();
        } else {
            return $this->testLines()->with('medicalTest')->get();
        }
    }

    // Get the provider model (pharmacy or laboratory)
    public function getProviderAttribute()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return $this->laboratory;
        }
        return $this->pharmacy;
    }

    // Get the provider name
    public function getProviderNameAttribute()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return $this->laboratory->name ?? 'N/A';
        }
        return $this->pharmacy->name ?? 'N/A';
    }

    // Get the provider ID
    public function getProviderIdAttribute()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return $this->laboratory_id;
        }
        return $this->pharmacy_id;
    }

    // Scope for medicine offers
    public function scopeMedicineOffers($query)
    {
        return $query->where('request_type', self::TYPE_MEDICINE);
    }

    // Scope for test offers
    public function scopeTestOffers($query)
    {
        return $query->where('request_type', self::TYPE_TEST);
    }

    // Scope for radiology offers
    public function scopeRadiologyOffers($query)
    {
        return $query->where('request_type', self::TYPE_RADIOLOGY);
    }

    // Scope for test and radiology offers (laboratory-based)
    public function scopeLaboratoryOffers($query)
    {
        return $query->whereIn('request_type', [self::TYPE_TEST, self::TYPE_RADIOLOGY]);
    }

    // Scope for pharmacy offers
    public function scopePharmacyOffers($query)
    {
        return $query->where('request_type', self::TYPE_MEDICINE);
    }

    // Get all possible request types
    public static function getRequestTypes()
    {
        return [
            self::TYPE_MEDICINE => 'Medicine',
            self::TYPE_TEST => 'Test',
            self::TYPE_RADIOLOGY => 'Radiology',
        ];
    }

    // Get all possible statuses
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_DRAFT => 'Draft',
        ];
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    // Type check helpers
    public function isMedicineOffer()
    {
        return $this->request_type === self::TYPE_MEDICINE;
    }

    public function isTestOffer()
    {
        return $this->request_type === self::TYPE_TEST;
    }

    public function isRadiologyOffer()
    {
        return $this->request_type === self::TYPE_RADIOLOGY;
    }

    public function isLaboratoryOffer()
    {
        return in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY]);
    }

    // Get request type label
    public function getTypeLabelAttribute()
    {
        return match($this->request_type) {
            self::TYPE_MEDICINE => 'Medicine',
            self::TYPE_TEST => 'Medical Test',
            self::TYPE_RADIOLOGY => 'Radiology',
            default => 'Unknown',
        };
    }

    // Get status label
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge bg-warning',
            self::STATUS_ACCEPTED => 'badge bg-success',
            self::STATUS_REJECTED => 'badge bg-danger',
            self::STATUS_DRAFT => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    // Get type badge class
    public function getTypeBadgeClassAttribute()
    {
        return match($this->request_type) {
            self::TYPE_MEDICINE => 'badge bg-success',
            self::TYPE_TEST => 'badge bg-info',
            self::TYPE_RADIOLOGY => 'badge bg-warning',
            default => 'badge bg-secondary',
        };
    }

    // Get type icon class
    public function getTypeIconAttribute()
    {
        return match($this->request_type) {
            self::TYPE_MEDICINE => 'fas fa-pills',
            self::TYPE_TEST => 'fas fa-vial',
            self::TYPE_RADIOLOGY => 'fas fa-x-ray',
            default => 'fas fa-question-circle',
        };
    }

    // Get appropriate provider ID based on type
    public function getProviderId()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return $this->laboratory_id;
        }
        return $this->pharmacy_id;
    }

    // Set appropriate provider based on type
    public function setProviderId($id)
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            $this->laboratory_id = $id;
        } else {
            $this->pharmacy_id = $id;
        }
    }

    // Calculate total price from lines
    public function calculateTotalPrice()
    {
        $total = 0;

        if ($this->request_type === self::TYPE_MEDICINE) {
            foreach ($this->medicineLines as $line) {
                $total += ($line->price * $line->quantity);
            }
        } else {
            foreach ($this->testLines as $line) {
                $total += $line->price;
            }
        }

        $total += $this->visit_price ?? 0;

        return $total;
    }

    // Get lines count
    public function getLinesCountAttribute()
    {
        if ($this->request_type === self::TYPE_MEDICINE) {
            return $this->medicineLines()->count();
        } else {
            return $this->testLines()->count();
        }
    }

    // Check if offer has home visit
    public function getHasHomeVisitAttribute()
    {
        return $this->visit_price > 0 ||
            ($this->request && $this->request->client_address_id);
    }

    // Get home visit price or status
    public function getHomeVisitInfoAttribute()
    {
        if ($this->visit_price > 0) {
            return 'EGP ' . number_format($this->visit_price, 2);
        } elseif ($this->request && $this->request->client_address_id) {
            return 'Free';
        }
        return 'Not Available';
    }

    // Get formatted total price
    public function getFormattedTotalPriceAttribute()
    {
        return 'EGP ' . number_format($this->total_price, 2);
    }

    // Get formatted visit price
    public function getFormattedVisitPriceAttribute()
    {
        return 'EGP ' . number_format($this->visit_price, 2);
    }

    // Get display name for the offer
    public function getDisplayNameAttribute()
    {
        $provider = $this->provider_name;
        $type = $this->type_label;
        $price = $this->formatted_total_price;

        return "{$type} Offer by {$provider} - {$price}";
    }

    // Check if offer can be edited (only if pending or draft)
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_DRAFT]);
    }

    // Check if offer can be deleted
    public function canBeDeleted()
    {
        return $this->status === self::STATUS_DRAFT ||
            ($this->status === self::STATUS_PENDING && auth()->check() && auth()->id() === $this->user_id);
    }

    // Accept the offer
    public function accept()
    {
        $this->update(['status' => self::STATUS_ACCEPTED]);
        return $this;
    }

    // Reject the offer
    public function reject()
    {
        $this->update(['status' => self::STATUS_REJECTED]);
        return $this;
    }

    // Mark as pending
    public function markAsPending()
    {
        $this->update(['status' => self::STATUS_PENDING]);
        return $this;
    }

    // Attachments relationship (polymorphic)
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Activity log for offers
    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    // Scope to get offers by provider
    public function scopeByProvider($query, $providerId, $providerType = null)
    {
        if ($providerType === 'laboratory') {
            return $query->where('laboratory_id', $providerId)
                ->whereIn('request_type', [self::TYPE_TEST, self::TYPE_RADIOLOGY]);
        } elseif ($providerType === 'pharmacy') {
            return $query->where('pharmacy_id', $providerId)
                ->where('request_type', self::TYPE_MEDICINE);
        }

        // Auto-detect provider type
        return $query->where(function($q) use ($providerId) {
            $q->where('laboratory_id', $providerId)
                ->orWhere('pharmacy_id', $providerId);
        });
    }

    // Scope to get offers by client request
    public function scopeByRequest($query, $requestId)
    {
        return $query->where('client_request_id', $requestId);
    }

    // Scope to get offers by user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope to get offers by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope to get offers by request type
    public function scopeByRequestType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    // Scope to get offers with all relationships
    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'request.client',
            'pharmacy',
            'laboratory',
            'user',
            'medicineLines.medicine',
            'testLines.medicalTest',
        ]);
    }

    // Get the appropriate provider model name
    public function getProviderModelNameAttribute()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return 'Laboratory';
        }
        return 'Pharmacy';
    }

    // Get the appropriate provider relationship name
    public function getProviderRelationNameAttribute()
    {
        if (in_array($this->request_type, [self::TYPE_TEST, self::TYPE_RADIOLOGY])) {
            return 'laboratory';
        }
        return 'pharmacy';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PatientTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'appointment_id', 'doctor_id', 'uploaded_by', 'type', 'title', 'notes',
    ];

    public const TYPES = ['lab', 'radiology'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** The clinic staff user who uploaded this test. */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Result files for this test (polymorphic attachments). */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function typeLabel(): string
    {
        $key = 'app.test_types.'.$this->type;

        return __($key) === $key ? ucfirst($this->type) : __($key);
    }
}

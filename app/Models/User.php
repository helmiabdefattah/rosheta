<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'pharmacy_id',
        'laboratory_id',
        'nurse_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The pharmacy this user belongs to.
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The laboratory this user belongs to.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * The nurse profile associated with this user.
     */
    public function nurse(): HasOne
    {
        return $this->hasOne(Nurse::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'test@example.com';
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->laboratory_id === null
            && $this->pharmacy_id === null
            && !$this->nurse()->exists();
    }
}

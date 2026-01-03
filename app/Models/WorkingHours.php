<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkingHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'workable_id',
        'workable_type',
        'default_opening_time',
        'default_closing_time',
        'sunday_status',
        'sunday_opening_time',
        'sunday_closing_time',
        'monday_status',
        'monday_opening_time',
        'monday_closing_time',
        'tuesday_status',
        'tuesday_opening_time',
        'tuesday_closing_time',
        'wednesday_status',
        'wednesday_opening_time',
        'wednesday_closing_time',
        'thursday_status',
        'thursday_opening_time',
        'thursday_closing_time',
        'friday_status',
        'friday_opening_time',
        'friday_closing_time',
        'saturday_status',
        'saturday_opening_time',
        'saturday_closing_time',
    ];

    protected $casts = [
        'default_opening_time' => 'datetime:H:i',
        'default_closing_time' => 'datetime:H:i',
        'sunday_opening_time' => 'datetime:H:i',
        'sunday_closing_time' => 'datetime:H:i',
        'monday_opening_time' => 'datetime:H:i',
        'monday_closing_time' => 'datetime:H:i',
        'tuesday_opening_time' => 'datetime:H:i',
        'tuesday_closing_time' => 'datetime:H:i',
        'wednesday_opening_time' => 'datetime:H:i',
        'wednesday_closing_time' => 'datetime:H:i',
        'thursday_opening_time' => 'datetime:H:i',
        'thursday_closing_time' => 'datetime:H:i',
        'friday_opening_time' => 'datetime:H:i',
        'friday_closing_time' => 'datetime:H:i',
        'saturday_opening_time' => 'datetime:H:i',
        'saturday_closing_time' => 'datetime:H:i',
    ];

    /**
     * Get the parent workable model (Laboratory, Pharmacy, etc.).
     */
    public function workable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the opening time for a specific day.
     */
    public function getOpeningTimeForDay(string $day): ?string
    {
        $status = $this->{$day . '_status'};
        
        if ($status === 'off') {
            return null;
        }
        
        if ($status === 'default') {
            return $this->default_opening_time ? $this->default_opening_time->format('H:i') : null;
        }
        
        // custom
        return $this->{$day . '_opening_time'} ? $this->{$day . '_opening_time'}->format('H:i') : null;
    }

    /**
     * Get the closing time for a specific day.
     */
    public function getClosingTimeForDay(string $day): ?string
    {
        $status = $this->{$day . '_status'};
        
        if ($status === 'off') {
            return null;
        }
        
        if ($status === 'default') {
            return $this->default_closing_time ? $this->default_closing_time->format('H:i') : null;
        }
        
        // custom
        return $this->{$day . '_closing_time'} ? $this->{$day . '_closing_time'}->format('H:i') : null;
    }

    /**
     * Check if a specific day is off.
     */
    public function isDayOff(string $day): bool
    {
        return $this->{$day . '_status'} === 'off';
    }
}

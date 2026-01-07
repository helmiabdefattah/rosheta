<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticketable_type',
        'ticketable_id',
        'subject',
        'message',
        'type',
        'priority',
        'status',
        'admin_response',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the parent ticketable model (Laboratory or Nurse).
     */
    public function ticketable()
    {
        return $this->morphTo();
    }

    /**
     * Get the admin user assigned to this ticket.
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get ticket type options.
     */
    public static function getTypeOptions()
    {
        return [
            'technical' => 'Technical Issue',
            'billing' => 'Billing',
            'feature_request' => 'Feature Request',
            'complaint' => 'Complaint',
            'other' => 'Other',
        ];
    }

    /**
     * Get ticket priority options.
     */
    public static function getPriorityOptions()
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * Get ticket status options.
     */
    public static function getStatusOptions()
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }
}

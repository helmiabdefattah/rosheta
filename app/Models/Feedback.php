<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'subject',
        'message',
        'type',
        'status',
        'admin_response',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the client that submitted the feedback.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get feedback type options.
     */
    public static function getTypeOptions()
    {
        return [
            'bug' => 'Bug Report',
            'suggestion' => 'Suggestion',
            'complaint' => 'Complaint',
            'compliment' => 'Compliment',
            'other' => 'Other',
        ];
    }

    /**
     * Get feedback status options.
     */
    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'reviewed' => 'Reviewed',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SupportTicket extends Model
{
    use HasFactory;

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the support ticket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to the support ticket.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include tickets with a specific priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if ticket is open.
     */
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if ticket is in progress.
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if ticket is resolved.
     */
    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
    }
}

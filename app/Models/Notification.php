<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_IN_APP = 'in_app';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'channel',
        'subject',
        'message',
        'status',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user that the notification belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Check if notification is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if notification is sent.
     */
    public function isSent()
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Check if notification failed.
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }
}

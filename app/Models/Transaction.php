<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory;

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_PAYMENT = 'payment';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'destination_account_id',
        'amount',
        'type',
        'status',
        'description',
        'reference',
        'initiated_by',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the account that owns the transaction.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the destination account for transfer transactions.
     */
    public function destinationAccount()
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    /**
     * Get the user who initiated the transaction.
     */
    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who approved the transaction.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved transactions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction is rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
}

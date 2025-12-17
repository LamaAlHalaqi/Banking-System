<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Transaction;

class Account extends Model
{
    use HasFactory;

    const TYPE_SAVINGS = 'savings';
    const TYPE_CHECKING = 'checking';
    const TYPE_LOAN = 'loan';
    const TYPE_INVESTMENT = 'investment';

    const STATE_ACTIVE = 'active';
    const STATE_FROZEN = 'frozen';
    const STATE_SUSPENDED = 'suspended';
    const STATE_CLOSED = 'closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_number',
        'account_type',
        'balance',
        'interest_rate',
        'state',
        'parent_account_id',
        'overdraft_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'overdraft_limit' => 'decimal:2',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent account.
     */
    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get the child accounts.
     */
    public function childAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Get the transactions for the account.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if account is active.
     */
    public function isActive()
    {
        return $this->state === self::STATE_ACTIVE;
    }

    /**
     * Check if account is frozen.
     */
    public function isFrozen()
    {
        return $this->state === self::STATE_FROZEN;
    }

    /**
     * Check if account is suspended.
     */
    public function isSuspended()
    {
        return $this->state === self::STATE_SUSPENDED;
    }

    /**
     * Check if account is closed.
     */
    public function isClosed()
    {
        return $this->state === self::STATE_CLOSED;
    }

    /**
     * Get account type name.
     */
    public function getTypeNameAttribute()
    {
        switch ($this->account_type) {
            case self::TYPE_SAVINGS:
                return 'Savings Account';
            case self::TYPE_CHECKING:
                return 'Checking Account';
            case self::TYPE_LOAN:
                return 'Loan Account';
            case self::TYPE_INVESTMENT:
                return 'Investment Account';
            default:
                return ucfirst($this->account_type);
        }
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Account;
use App\Models\Transaction;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_CUSTOMER = 'customer';
    const ROLE_TELLER = 'teller';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the accounts for the user.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the transactions initiated by the user.
     */
    public function initiatedTransactions()
    {
        return $this->hasMany(Transaction::class, 'initiated_by');
    }

    /**
     * Get the transactions approved by the user.
     */
    public function approvedTransactions()
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer()
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user is a teller.
     */
    public function isTeller()
    {
        return $this->role === self::ROLE_TELLER;
    }

    /**
     * Check if user is a manager.
     */
    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }
}

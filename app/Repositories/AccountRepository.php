<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountRepository
{
    /**
     * Get all accounts for a user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserAccounts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->accounts()->paginate($perPage);
    }

    /**
     * Find an account by ID.
     *
     * @param int $id
     * @return Account|null
     */
    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    /**
     * Find an account by account number.
     *
     * @param string $accountNumber
     * @return Account|null
     */
    public function findByAccountNumber(string $accountNumber): ?Account
    {
        return Account::where('account_number', $accountNumber)->first();
    }

    /**
     * Get accounts by type.
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType(string $type)
    {
        return Account::where('account_type', $type)->get();
    }

    /**
     * Get accounts by state.
     *
     * @param string $state
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByState(string $state)
    {
        return Account::where('state', $state)->get();
    }
}
<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository
{
    /**
     * Get all transactions for an account.
     *
     * @param Account $account
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAccountTransactions(Account $account, int $perPage = 15): LengthAwarePaginator
    {
        return $account->transactions()->paginate($perPage);
    }

    /**
     * Get all transactions for a user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTransactions(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->paginate($perPage);
    }

    /**
     * Find a transaction by ID.
     *
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    /**
     * Get pending transactions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingTransactions()
    {
        return Transaction::pending()->get();
    }

    /**
     * Get transactions by type.
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType(string $type)
    {
        return Transaction::where('type', $type)->get();
    }

    /**
     * Get transactions by status.
     *
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus(string $status)
    {
        return Transaction::where('status', $status)->get();
    }
}
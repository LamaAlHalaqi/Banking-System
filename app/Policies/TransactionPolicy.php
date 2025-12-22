<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->account->user_id || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAdmin() || $user->isTeller();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->initiated_by || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the transaction.
     */
    public function approve(User $user, Transaction $transaction): bool
    {
        // Admins can approve transactions > 1000
        if ($user->isAdmin()) {
            return $transaction->amount > 1000;
        }

        // Managers can approve transactions > 500
        if ($user->isManager()) {
            return $transaction->amount > 500;
        }

        // Other users cannot approve transactions
        return false;
    }

    /**
     * Determine whether the user can reject the transaction.
     */
    public function reject(User $user, Transaction $transaction): bool
    {
        // Admins can reject transactions > 1000
        if ($user->isAdmin()) {
            return $transaction->amount > 1000;
        }

        // Managers can reject transactions > 500
        if ($user->isManager()) {
            return $transaction->amount > 500;
        }

        // Other users cannot reject transactions
        return false;
    }
}

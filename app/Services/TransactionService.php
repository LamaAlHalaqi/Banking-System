<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Get paginated transactions for an account.
     *
     * @param Account $account
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAccountTransactions(Account $account, int $perPage = 15): LengthAwarePaginator
    {
        return $account->transactions()->with(['account', 'destinationAccount'])->paginate($perPage);
    }

    /**
     * Get paginated transactions for a user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTransactions(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['account', 'destinationAccount'])->paginate($perPage);
    }

    /**
     * Get transaction by ID.
     *
     * @param int $id
     * @return Transaction|null
     */
    public function getTransactionById(int $id): ?Transaction
    {
        return Transaction::with(['account', 'destinationAccount', 'initiatedBy', 'approvedBy'])->find($id);
    }

    /**
     * Approve a pending transaction.
     *
     * @param Transaction $transaction
     * @param User $approvingUser
     * @return Transaction
     */
    public function approveTransaction(Transaction $transaction, User $approvingUser): Transaction
    {
        if (!$transaction->isPending()) {
            throw new \InvalidArgumentException('Transaction is not pending approval.');
        }

        // Ensure the transaction actually requires approval (greater than threshold)
        if (!$transaction->requiresApproval()) {
            throw new \InvalidArgumentException('Transaction does not require approval.');
        }

        return DB::transaction(function () use ($transaction, $approvingUser) {
            // First, update transaction status to approved
            $transaction->update([
                'status' => Transaction::STATUS_APPROVED,
                'approved_by' => $approvingUser->id,
            ]);

            // Process the transaction based on its type
            switch ($transaction->type) {
                case Transaction::TYPE_DEPOSIT:
                    // Add funds to account
                    $transaction->account->increment('balance', $transaction->amount);
                    break;

                case Transaction::TYPE_WITHDRAWAL:
                    // Deduct funds from account
                    $transaction->account->decrement('balance', $transaction->amount);
                    break;

                case Transaction::TYPE_TRANSFER:
                    // Process transfer between accounts
                    if ($transaction->destination_account_id) {
                        $fromAccount = $transaction->account;
                        $toAccount = $transaction->destinationAccount;

                        // Deduct from source account
                        $fromAccount->decrement('balance', $transaction->amount);

                        // Add to destination account
                        $toAccount->increment('balance', $transaction->amount);
                    }
                    break;
            }

            // Update transaction status to completed
            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
            ]);

            return $transaction;
        });
    }

    /**
     * Reject a pending transaction.
     *
     * @param Transaction $transaction
     * @param User $rejectingUser
     * @return Transaction
     */
public function rejectTransaction(Transaction $transaction, User $rejectingUser): Transaction
{
    if (!$transaction->isPending()) {
        throw new \InvalidArgumentException('Transaction is not pending approval.');
    }

    // Ensure the transaction actually requires approval
    if (!$transaction->requiresApproval()) {
        throw new \InvalidArgumentException('Transaction does not require approval.');
    }

    return DB::transaction(function () use ($transaction, $rejectingUser) {
        // فقط تغيير الحالة وربطها بالمستخدم الرافض
        $transaction->update([
            'status' => Transaction::STATUS_REJECTED,
            'approved_by' => $rejectingUser->id,
        ]);

        // لا تعديل على الأرصدة هنا

        return $transaction;
    });
}


    /**
     * Get daily transactions report.
     *
     * @param string|null $date
     * @return array
     */
    public function getDailyTransactionsReport(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        $transactions = Transaction::whereDate('created_at', $date)
            ->with(['account.user'])
            ->get();

        $totalAmount = $transactions->sum('amount');
        $transactionCount = $transactions->count();

        return [
            'date' => $date,
            'total_amount' => $totalAmount,
            'transaction_count' => $transactionCount,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get account summaries report.
     *
     * @return array
     */
    public function getAccountSummariesReport(): array
    {
        $accounts = Account::with('user')->get();

        $totalAccounts = $accounts->count();
        $totalBalance = $accounts->sum('balance');

        // Group by account type
        $accountTypeSummary = $accounts->groupBy('account_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_balance' => $group->sum('balance'),
            ];
        });

        return [
            'total_accounts' => $totalAccounts,
            'total_balance' => $totalBalance,
            'by_type' => $accountTypeSummary,
        ];
    }
}

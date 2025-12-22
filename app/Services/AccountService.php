<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    /**
     * Create a new account for a user.
     *
     * @param User $user
     * @param array $data
     * @return Account
     */
    public function createAccount(User $user, array $data): Account
    {
        // Generate unique account number
        $accountNumber = $this->generateUniqueAccountNumber();

        $account = Account::create([
            'user_id' => $user->id,
            'account_number' => $accountNumber,
            'account_type' => $data['account_type'],
            'balance' => $data['initial_deposit'] ?? 0,
            'interest_rate' => $data['interest_rate'] ?? 0,
            'overdraft_limit' => $data['overdraft_limit'] ?? 0,
            'state' => Account::STATE_ACTIVE,
             'parent_account_id' => $data['parent_account_id'] ?? null,
        ]);

        // If initial deposit was provided, create a transaction
        if (isset($data['initial_deposit']) && $data['initial_deposit'] > 0) {
            Transaction::create([
                'account_id' => $account->id,
                'amount' => $data['initial_deposit'],
                'type' => Transaction::TYPE_DEPOSIT,
                'status' => Transaction::STATUS_COMPLETED,
                'description' => 'Initial deposit',
                'reference' => uniqid(),
                'initiated_by' => $user->id,
            ]);
        }

        return $account;
    }

    /**
     * Generate a unique account number.
     *
     * @return string
     */
    private function generateUniqueAccountNumber(): string
    {
        do {
            $accountNumber = 'ACC' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Deposit funds into an account.
     *
     * @param Account $account
     * @param float $amount
     * @param string|null $description
     * @return Transaction
     */
    public function deposit(Account $account, float $amount, ?string $description = null): Transaction
    {
        if (!$account->isActive()) {
            throw ValidationException::withMessages([
                'account' => ['Cannot deposit to inactive account.'],
            ]);
        }

        // If below or equal to 500, process immediately
        if ($amount <= Transaction::MANAGER_APPROVAL_THRESHOLD) {
            return DB::transaction(function () use ($account, $amount, $description) {
                $transaction = Transaction::create([
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'type' => Transaction::TYPE_DEPOSIT,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => $description ?? 'Deposit',
                    'reference' => uniqid(),
                    'initiated_by' => $account->user_id,
                    'approved_by' => $account->user_id, // auto-approved
                ]);

                // Apply balance immediately
                $account->increment('balance', $amount);

                return $transaction;
            });
        }

        // Create transaction record without updating balance immediately
        $transaction = Transaction::create([
            'account_id' => $account->id,
            'amount' => $amount,
            'type' => Transaction::TYPE_DEPOSIT,
            'status' => Transaction::STATUS_PENDING,
            'description' => $description ?? 'Deposit',
            'reference' => uniqid(),
            'initiated_by' => $account->user_id,
        ]);

        return $transaction;
    }

    /**
     * Withdraw funds from an account.
     *
     * @param Account $account
     * @param float $amount
     * @param string|null $description
     * @return Transaction
     */
    public function withdraw(Account $account, float $amount, ?string $description = null): Transaction
    {
        if (!$account->isActive()) {
            throw ValidationException::withMessages([
                'account' => ['Cannot withdraw from inactive account.'],
            ]);
        }

        // Check if sufficient funds (considering overdraft limit for checking accounts)
        $availableBalance = $account->balance + ($account->account_type === Account::TYPE_CHECKING ? $account->overdraft_limit : 0);

        if ($availableBalance < $amount) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient funds.'],
            ]);
        }

        // If below or equal to 500, process immediately
        if ($amount <= Transaction::MANAGER_APPROVAL_THRESHOLD) {
            return DB::transaction(function () use ($account, $amount, $description) {
                $transaction = Transaction::create([
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'type' => Transaction::TYPE_WITHDRAWAL,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => $description ?? 'Withdrawal',
                    'reference' => uniqid(),
                    'initiated_by' => $account->user_id,
                    'approved_by' => $account->user_id, // auto-approved
                ]);

                // Apply balance immediately
                $account->decrement('balance', $amount);

                return $transaction;
            });
        }

        // Create transaction record without updating balance immediately
        $transaction = Transaction::create([
            'account_id' => $account->id,
            'amount' => $amount,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'status' => Transaction::STATUS_PENDING,
            'description' => $description ?? 'Withdrawal',
            'reference' => uniqid(),
            'initiated_by' => $account->user_id,
        ]);

        return $transaction;
    }

    /**
     * Transfer funds between accounts.
     *
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param float $amount
     * @param string|null $description
     * @return Transaction
     */
    public function transfer(Account $fromAccount, Account $toAccount, float $amount, ?string $description = null): Transaction
    {
        if (!$fromAccount->isActive()) {
            throw ValidationException::withMessages([
                'from_account' => ['Cannot transfer from inactive account.'],
            ]);
        }

        if (!$toAccount->isActive()) {
            throw ValidationException::withMessages([
                'to_account' => ['Cannot transfer to inactive account.'],
            ]);
        }

        // Check if sufficient funds (considering overdraft limit for checking accounts)
        $availableBalance = $fromAccount->balance + ($fromAccount->account_type === Account::TYPE_CHECKING ? $fromAccount->overdraft_limit : 0);

        if ($availableBalance < $amount) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient funds.'],
            ]);
        }

        // If below or equal to 500, process immediately
        if ($amount <= Transaction::MANAGER_APPROVAL_THRESHOLD) {
            return DB::transaction(function () use ($fromAccount, $toAccount, $amount, $description) {
                $transaction = Transaction::create([
                    'account_id' => $fromAccount->id,
                    'destination_account_id' => $toAccount->id,
                    'amount' => $amount,
                    'type' => Transaction::TYPE_TRANSFER,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => $description ?? 'Transfer',
                    'reference' => uniqid(),
                    'initiated_by' => $fromAccount->user_id,
                    'approved_by' => $fromAccount->user_id, // auto-approved
                ]);

                // Apply balances immediately
                $fromAccount->decrement('balance', $amount);
                $toAccount->increment('balance', $amount);

                return $transaction;
            });
        }

        // Create transaction record without updating balances immediately
        $transaction = Transaction::create([
            'account_id' => $fromAccount->id,
            'destination_account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => Transaction::TYPE_TRANSFER,
            'status' => Transaction::STATUS_PENDING,
            'description' => $description ?? 'Transfer',
            'reference' => uniqid(),
            'initiated_by' => $fromAccount->user_id,
        ]);

        return $transaction;
    }

    /**
     * Close an account.
     *
     * @param Account $account
     * @return Account
     */
    public function closeAccount(Account $account): Account
    {
        if ($account->balance != 0) {
            throw ValidationException::withMessages([
                'account' => ['Cannot close account with non-zero balance.'],
            ]);
        }

        $account->update([
            'state' => Account::STATE_CLOSED,
        ]);

        return $account;
    }
}

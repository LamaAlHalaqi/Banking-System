<?php

namespace App\Actions\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Simple command/action to change an account's state.
 * Implements a small Command pattern (encapsulates the request to change state).
 */
class ChangeAccountState
{
    protected Account $account;
    protected string $state;
    protected User $performedBy;

    public function __construct(Account $account, string $state, User $performedBy)
    {
        $this->account = $account;
        $this->state = $state;
        $this->performedBy = $performedBy;
    }

    /**
     * Execute the action
     *
     * @return Account
     * @throws \InvalidArgumentException
     */
    public function execute(): Account
    {
        $allowed = [
            Account::STATE_ACTIVE,
            Account::STATE_FROZEN,
            Account::STATE_SUSPENDED,
        ];

        if (!in_array($this->state, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid account state: ' . $this->state);
        }

        if ($this->account->isClosed()) {
            throw new \InvalidArgumentException('Cannot change state of a closed account.');
        }

        // Optionally you could add auditing/logging here (not implemented)
        $this->account->update([
            'state' => $this->state,
        ]);

        return $this->account;
    }
}

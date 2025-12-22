<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManagerController extends Controller
{
    use AuthorizesRequests;

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * List pending transactions for managers.
     *
     * Supports pagination via ?per_page=
     */
    public function pendingTransactions(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $transactions = Transaction::pending()
            ->where('amount', '>', Transaction::MANAGER_APPROVAL_THRESHOLD)
            ->where('amount', '<=', Transaction::ADMIN_APPROVAL_THRESHOLD) // Only transactions that don't require admin approval
            ->with(['account.user', 'initiatedBy'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return TransactionResource::collection($transactions);
    }

    /**
     * Approve a transaction.
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function approveTransaction(Request $request, Transaction $transaction)
    {
        // Check if user is authorized to approve this transaction
        $this->authorize('approve', $transaction);

        try {
            $approvedTransaction = $this->transactionService->approveTransaction($transaction, $request->user());
            return new TransactionResource($approvedTransaction);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function rejectTransaction(Request $request, Transaction $transaction)
    {
        // Check if user is authorized to reject this transaction
        $this->authorize('reject', $transaction);

        try {
            $rejectedTransaction = $this->transactionService->rejectTransaction($transaction, $request->user());
            return new TransactionResource($rejectedTransaction);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\AccountResource;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{

     use AuthorizesRequests;

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get admin dashboard data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $totalTransactions = Transaction::count();
        $totalAmount = Transaction::sum('amount');
        $pendingTransactions = Transaction::pending()->count();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'pending_transactions' => $pendingTransactions,
        ]);
    }

    /**
     * Get manager dashboard data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function managerDashboard(Request $request)
    {
        $pendingTransactions = Transaction::pending()->with(['account.user'])->get();

        return response()->json([
            'pending_transactions' => TransactionResource::collection($pendingTransactions),
        ]);
    }

    /**
     * List all user accounts for admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAllAccounts(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $accounts = Account::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return AccountResource::collection($accounts);
    }

    /**
     * List pending transactions for admins.
     *
     * Supports pagination via ?per_page=
     */
    public function pendingTransactions(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $transactions = Transaction::pending()
            ->where('amount', '>', Transaction::ADMIN_APPROVAL_THRESHOLD)
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


    /**
     * Get daily transactions report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyTransactionsReport(Request $request)
    {
        $date = $request->query('date');
        $report = $this->transactionService->getDailyTransactionsReport($date);

        return response()->json($report);
    }

    /**
     * Get account summaries report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountSummariesReport(Request $request)
    {
        $report = $this->transactionService->getAccountSummariesReport();

        return response()->json($report);
    }

    /**
     * Get audit logs report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auditLogsReport(Request $request)
    {
        // For now, we'll return a simple audit log report
        // In a real application, this would be more comprehensive
        $recentTransactions = Transaction::with(['account.user', 'initiatedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'transactions' => TransactionResource::collection($recentTransactions),
        ]);
    }

    /**
     * Change account state (suspend / freeze / unfreeze) — admin only
     *
     * POST /admin/accounts/{account}/state
     * body: { state: 'suspended' }
     */
    public function changeAccountState(Request $request, \App\Models\Account $account)
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'state' => ['required', 'string', 'in:'.implode(',', [\App\Models\Account::STATE_ACTIVE, \App\Models\Account::STATE_FROZEN, \App\Models\Account::STATE_SUSPENDED])],
        ]);

        try {
            $action = new \App\Actions\Account\ChangeAccountState($account, $validated['state'], $request->user());
            $result = $action->execute();

            return response()->json([
                'message' => 'Account state updated',
                'account' => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to change account state'], 500);
        }
    }
}

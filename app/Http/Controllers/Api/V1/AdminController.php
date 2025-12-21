<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\AccountResource;
use App\Models\Transaction;
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
     * List pending transactions for admins.
     *
     * Supports pagination via ?per_page=
     */
    public function pendingTransactions(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $transactions = Transaction::pending()
            ->where('amount', '>', Transaction::APPROVAL_THRESHOLD)
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
}

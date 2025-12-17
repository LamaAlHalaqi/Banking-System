<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Requests\TransferRequest;
use App\Services\AccountService;
use App\Services\NotificationService;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AccountController extends Controller
{
      use AuthorizesRequests; 
    protected $accountService;
    protected $notificationService;

    public function __construct(AccountService $accountService, NotificationService $notificationService)
    {
        $this->accountService = $accountService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $accounts = $request->user()->accounts()->with(['user', 'parentAccount', 'childAccounts'])->paginate(15);
        return AccountResource::collection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AccountRequest $request
     * @return AccountResource
     */
    public function store(AccountRequest $request)
    {
        $account = $this->accountService->createAccount($request->user(), $request->validated());
        return new AccountResource($account->load(['user', 'parentAccount', 'childAccounts']));
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return AccountResource
     */
    public function show(Account $account)
    {
        $this->authorize('view', $account);
        return new AccountResource($account->load(['user', 'parentAccount', 'childAccounts', 'transactions']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Account $account
     * @return AccountResource
     */
    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $account->update($request->only(['interest_rate', 'overdraft_limit']));
        return new AccountResource($account->load(['user', 'parentAccount', 'childAccounts']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        try {
            $this->accountService->closeAccount($account);
            return response()->json(['message' => 'Account closed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Deposit funds into an account.
     *
     * @param DepositRequest $request
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(DepositRequest $request, Account $account)
    {
        $this->authorize('update', $account);

        try {
            $transaction = $this->accountService->deposit($account, $request->amount, $request->description);

            // Send notification
            $this->notificationService->sendAccountActivityNotification($account, $transaction);

            return response()->json([
                'message' => 'Deposit successful',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Withdraw funds from an account.
     *
     * @param WithdrawRequest $request
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(WithdrawRequest $request, Account $account)
    {
        $this->authorize('update', $account);

        try {
            $transaction = $this->accountService->withdraw($account, $request->amount, $request->description);

            // Send notification
            $this->notificationService->sendAccountActivityNotification($account, $transaction);

            return response()->json([
                'message' => 'Withdrawal successful',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Transfer funds between accounts.
     *
     * @param TransferRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfer(TransferRequest $request)
    {
        $fromAccount = Account::findOrFail($request->from_account_id);
        $toAccount = Account::findOrFail($request->to_account_id);

        $this->authorize('update', $fromAccount);

        try {
            $transaction = $this->accountService->transfer($fromAccount, $toAccount, $request->amount, $request->description);

            // Send notifications to both accounts
            $this->notificationService->sendAccountActivityNotification($fromAccount, $transaction);
            $this->notificationService->sendAccountActivityNotification($toAccount, $transaction);

            return response()->json([
                'message' => 'Transfer successful',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

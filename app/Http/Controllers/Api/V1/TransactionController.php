<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class TransactionController extends Controller
{
      use AuthorizesRequests; 
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $transactions = $this->transactionService->getUserTransactions($request->user());
        return TransactionResource::collection($transactions);
    }

    /**
     * Display the specified resource.
     *
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return new TransactionResource($transaction);
    }
}

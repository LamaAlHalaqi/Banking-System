<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'account_number' => $this->account_number,
            'account_type' => $this->account_type,
            'account_type_name' => $this->type_name,
            'balance' => $this->balance,
            'interest_rate' => $this->interest_rate,
            'state' => $this->state,
            'parent_account_id' => $this->parent_account_id,
            'overdraft_limit' => $this->overdraft_limit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'parent_account' => new AccountResource($this->whenLoaded('parentAccount')),
            'child_accounts' => AccountResource::collection($this->whenLoaded('childAccounts')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}

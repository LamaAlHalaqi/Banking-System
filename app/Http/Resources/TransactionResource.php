<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'account_id' => $this->account_id,
            'destination_account_id' => $this->destination_account_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'reference' => $this->reference,
            'initiated_by' => $this->initiated_by,
            'approved_by' => $this->approved_by,
            'scheduled_at' => $this->scheduled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'account' => new AccountResource($this->whenLoaded('account')),
            'destination_account' => new AccountResource($this->whenLoaded('destinationAccount')),
            'initiated_by_user' => new UserResource($this->whenLoaded('initiatedBy')),
            'approved_by_user' => new UserResource($this->whenLoaded('approvedBy')),
        ];
    }
}

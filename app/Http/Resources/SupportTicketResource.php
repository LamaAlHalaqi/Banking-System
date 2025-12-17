<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
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
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'assigned_to_user' => new UserResource($this->whenLoaded('assignedTo')),
        ];
    }
}
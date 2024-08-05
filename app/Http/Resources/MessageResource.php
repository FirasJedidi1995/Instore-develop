<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'message' => $this->message,
            'sender_id' => new UserResource(User::find($this->sender_id)),
            'receiver_id'=> new UserResource(User::find($this->receiver_id)),
            'sender_type'=> $this->sender_type,
        ];
    }
}

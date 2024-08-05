<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\Product;


class EchantillonResource extends JsonResource
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
            'payment' => $this->payment,
            'instagrammer_id' => new UserResource(User::find($this->instagrammer_id)),
            'provider_id' => new UserResource(User::find($this->provider_id)),
            'product_id'=> new ProductResource(Product::find($this->product_id)),
            'status' => $this->status,

        ];
    }
}

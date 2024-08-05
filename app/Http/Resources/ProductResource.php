<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'priceSale' => $this->priceSale,
            'priceFav' => $this->priceFav,
            'priceMax' => $this->priceMax,
            'status' => $this->status,
            'category' =>$this->category,
            'reference' =>$this->reference,
            'colors' =>$this->colors,
            'sizes' =>$this->sizes,
            'images' =>$this->images,
            'echantillon' =>$this->echantillon,
            'brand' =>$this->brand,
            'subcategories' =>$this->subcategories,
            'instagrammer_id' => new UserResource(User::find($this->instagrammer_id)),
            'provider_id'=> new UserResource(User::find($this->provider_id)),
        ];
        
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'color' => $this->color,
            'size' => $this->size,
            'city' => $this->city,
            'street' => $this->street,
            'post_code' => $this->post_code,
            'cardNumber' => $this->cardNumber,
            'securityCode' => $this->securityCode,
            'CVV' => $this->CVV,
            'quantity' => $this->quantity,
            'payment' => $this->payment,
            'invoice_link' =>$this->invoice_link,
            'product_id' => new ProductResource(Product::find($this->product_id)),
            //'shippingCost' => $this->shippingCost,
            //'TVA' => $this->TVA,
            //'totalProduct'=> $this->totalProduct,
            'totalPrice'=> $this->totalPrice
        ];
    }
}
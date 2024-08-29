<?php

namespace App\Http\Resources;

use App\Models\subCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if(!$this->resource){
            return [];
        }
        $subcategories=subCategory::where('category_id',$this->id)->get();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subcategories'=>SubCategoryResource::collection($subcategories)
        ];
        
    }
}
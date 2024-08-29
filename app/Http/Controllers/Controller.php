<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function searchProduct(Request $request)
    {

        $search = $request->has('search') ? $request->input('search') : "";
        $category = $request->has('category') ? $request->input('category') : "";

       
        $products = Product::where('category', 'like', '%' . $category . '%')
            ->where(function ($q) use ($search) {

                $q->Where('name', 'LIKE', "%{$search}%");
                   
            })
            ->get();


        return response()->json($products);
    }
   


public function filterProduct(Request $request)
{
   
    $categoryName = $request->input('category');
    $subcategoryName = $request->input('subcategory');
    $brandName = $request->input('brand');
    $name = $request->input('name');

   
    $query = Product::query();

    
    if ($categoryName) {
        $query->whereHas('subcategory.category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });
    }

    
    if ($subcategoryName) {
        $query->whereHas('subcategory', function($q) use ($subcategoryName) {
            $q->where('name', 'like', '%' . $subcategoryName . '%');
        });
    }

    
    if ($brandName) {
        $query->whereHas('brand', function($q) use ($brandName) {
            $q->where('name', 'like', '%' . $brandName . '%');
        });
    }

    
    if ($name) {
        $query->where('name', 'like', '%' . $name . '%');
    }

    $products = $query->get();

    return response()->json($products);
}



}
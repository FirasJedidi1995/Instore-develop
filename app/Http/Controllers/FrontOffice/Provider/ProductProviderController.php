<?php

namespace App\Http\Controllers\FrontOffice\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Color;
use App\Models\Echantillon;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Str; 
use App\Models\SubCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:provider-extern');

    }

    public function index()
    {
        $user = auth()->user();
        $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])
                            ->where('provider_id', $user->id)
                            ->get();
    
        return response()->json($products, Response::HTTP_OK);
    }
    
    public function store(Request $request)
    {
        
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0|required_without_all:combinations',
            'priceSale' => 'required|numeric|min:0',
            'priceFav' => 'nullable|numeric|min:0',
            'priceMax' => 'nullable|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'brand_id' => 'required|exists:brands,id',
            'echantillon' => 'nullable|in:FREE,PAID,REFUNDED',
            'combinations' => 'nullable|array',
            'combinations.*.size' => 'nullable|string|max:255',
            'combinations.*.color' => 'nullable|string|max:255',
            'combinations.*.quantity' => 'required_with:combinations|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
        ];
        
        $validatedData = $request->validate($rules);
    

        $user = auth()->user();
        $reference = Str::random(8);
        $product = new Product();
        $product->name = $validatedData['name'];
        $product->description = $validatedData['description'] ?? null;
        $product->quantity = $validatedData['quantity'] ?? 0;
        $product->priceSale = $validatedData['priceSale'];
        $product->priceFav = $validatedData['priceFav'] ?? null;
        $product->priceMax = $validatedData['priceMax'] ?? null;
        $product->reference = $reference;
        $product->subcategory_id = $validatedData['subcategory_id'];
        $product->brand_id = $validatedData['brand_id'];
        $product->echantillon = $validatedData['echantillon'] ?? null;
        $product->provider_id = $user->id;
        $product->save();
    
        $totalQuantity = 0;
    
        if (!empty($validatedData['combinations'])) {
            foreach ($validatedData['combinations'] as $combination) {
                $sizeId = null;
                $colorId = null;
    
                if (!empty($combination['size'])) {
                    $size = Size::firstOrCreate(['name' => $combination['size']]);
                    $sizeId = $size->id;
                }
    
                if (!empty($combination['color'])) {
                    $color = Color::firstOrCreate(['name' => $combination['color']]);
                    $colorId = $color->id;
                }
                    $product->sizes()->attach($sizeId, [
                    'color_id' => $colorId,
                    'quantity' => $combination['quantity'],
                ]);
    
                $totalQuantity += $combination['quantity'];
            }
        }
    
        if ($totalQuantity > 0) {
            $product->quantity = $totalQuantity;
            //$product->save();
        }else{
            $product->quantity=$validatedData['quantity'];
        }
        $product->status = $validatedData['status'] ?? ($product->quantity > 0 ? 'INSTOCK' : 'OUTSTOCK');
        
            if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $imagePath = 'images/' . $imageName;
                $image->move(public_path('images'), $imageName);
                $product->images()->create(['path' => asset($imagePath)]);
            }
        }
        $product->save();
        return response()->json($product, Response::HTTP_CREATED);
    }
    

    
    


    // en marche
    // public function store(Request $request)
    // {
    //     $rules = [
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'quantity' => 'required|integer|min:0',
    //         'priceSale' => 'required|numeric|min:0',
    //         'priceFav' => 'nullable|numeric|min:0',
    //         'priceMax' => 'nullable|numeric|min:0',
    //         'subcategory_id' => 'required|exists:subcategories,id',
    //         'brand_id' => 'required|exists:brands,id',
    //         'echantillon' => 'nullable|in:FREE,PAID,REFUNDED',
    //     ];
        
    //     $validatedData = $request->validate($rules);
    //     $user = auth()->user(); 
    //     $reference = Str::random(8);
    //     $product = new Product();
    //     $product->name = $validatedData['name'];
    //     $product->description = $validatedData['description'] ?? null;
    //     $product->quantity = $validatedData['quantity'];
    //     $product->priceSale = $validatedData['priceSale'];
    //     $product->priceFav = $validatedData['priceFav'] ?? null;
    //     $product->priceMax = $validatedData['priceMax'] ?? null;
    //     $product->reference = $reference;
    //     $product->subcategory_id = $validatedData['subcategory_id'];
    //     $product->brand_id = $validatedData['brand_id'];
    //     $product->echantillon = $validatedData['echantillon'] ?? null;
    //     $product->admin_id = $user->id;
    //     $product->save();
        
    
    //     return response()->json($product, Response::HTTP_CREATED);
    // }
    


    public function show($id)
{
    $user = auth()->user();
    $product = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])
        ->where('id', $id)
        ->where('provider_id', $user->id)
        ->first();

    if (!$product) {
        return response()->json(['error' => 'Product not found or not accessible'], Response::HTTP_NOT_FOUND);
    }

    return response()->json($product, Response::HTTP_OK);
}


    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $product = Product::where('id', $id)
            ->where('provider_id', $user->id)
            ->first();
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0|required_without_all:combinations',
            'priceSale' => 'sometimes|required|numeric|min:0',
            'priceFav' => 'nullable|numeric|min:0',
            'priceMax' => 'nullable|numeric|min:0',
            'subcategory_id' => 'sometimes|required|exists:subcategories,id',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'echantillon' => 'nullable|in:FREE,PAID,REFUNDED',
            'combinations' => 'nullable|array',
            'combinations.*.size' => 'nullable|string|max:255',
            'combinations.*.color' => 'nullable|string|max:255',
            'combinations.*.quantity' => 'required_with:combinations|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
        ];
    
        $validatedData = $request->validate($rules);
        //dd($request->getContent());
        $product->name = array_key_exists('name', $validatedData) ? $validatedData['name'] : $product->name;
        $product->description = $validatedData['description'] ?? null;
        $product->priceSale = $validatedData['priceSale'];
        $product->priceFav = $validatedData['priceFav'] ?? null;
        $product->priceMax = $validatedData['priceMax'] ?? null;
        $totalQuantity = 0;
    
        if (!empty($validatedData['combinations'])) {
            
            $product->sizes()->detach();
    
            foreach ($validatedData['combinations'] as $combination) {
                $sizeId = null;
                $colorId = null;
    
                if (!empty($combination['size'])) {
                    $size = Size::firstOrCreate(['name' => $combination['size']]);
                    $sizeId = $size->id;
                }
    
                if (!empty($combination['color'])) {
                    $color = Color::firstOrCreate(['name' => $combination['color']]);
                    $colorId = $color->id;
                }
    
                $product->sizes()->attach($sizeId, [
                    'color_id' => $colorId,
                    'quantity' => $combination['quantity'],
                ]);
    
                $totalQuantity += $combination['quantity'];
            }
        }
    
        if ($totalQuantity > 0) {
            $product->quantity = $totalQuantity;
            //$product->save();
        }else{
            $product->quantity=$validatedData['quantity'];
        }
    
        $product->status = $validatedData['status'] ?? ($product->quantity > 0 ? 'INSTOCK' : 'OUTSTOCK');
    
        
        if ($request->hasFile('images')) {
            
            $product->images()->delete();
            
            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $imagePath = 'images/' . $imageName;
                $image->move(public_path('images'), $imageName);
                $product->images()->create(['path' => asset($imagePath)]);
            }
        }
        $product->save();
        return response()->json($product, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $product = Product::where('id', $id)
            ->where('provider_id', $user->id)
            ->first();
    
        if (!$product) {
            return response()->json(['error' => 'Product not found or not accessible'], Response::HTTP_NOT_FOUND);
        }
    
        $product->delete();
    
        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }
    
}
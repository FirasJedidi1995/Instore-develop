<?php

namespace App\Http\Controllers\FrontOffice\Instagrammer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Color;
use App\Models\Echantillon;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use App\Models\Store;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 


class ProductInstagrammerController extends Controller
{

      public function __construct()
    {
        $this->middleware('role:provider-intern');

    }

    // public function index()
    // {
    //     $products = Product::all();
    //     return response()->json($products); 
    // }  
    public function colors()
    {
        $colors = Color::all();
        return response()->json(($colors)
        );
    }   
    public function sizes()
    {
        $sizes = Size::all();
        return response()->json($sizes)
        ;
    } 
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required',
            'quantity' => 'required|numeric',
            'priceSale' => 'required|numeric',
            'brand' => 'required|string',
            'category' => ['required', 'in:CLOTHING,ACCESSOIRIES,HOME,SPORT,BEAUTY,ELECTRONICS,PETS'],
            'status' => ['required', 'in:INSTOCK,OUTSTOCK'],
            
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->quantity = $request->quantity;
        $product->priceSale = $request->priceSale;
        $product->category = $request->category;
        $product->status = $request->status;
        $product->instagrammer_id =Auth::user()->id;
        $product->brand = $request->brand;
        $product->category = $request->category;
        $product->status = $request->status;
        $product->reference = Str::random(8);

        $product->save();

        if($request->subcategories){
            $subcategorie  = SubCategory::find($request->subcategories);          
            if ($subcategorie->type === $request->category) {
                $product->subcategories()->attach($subcategorie); 
            } 
        }
        // foreach ($request->colors as $color_id) {
        //     $color = Color::find($color_id);

        if($request->colors){
            $product->colors()->attach($request->colors);
        }

        // }

        // foreach ($request->sizes as $size_id) {
        //     $size = Size::find($size_id);
        if($request->sizes){  
            $product->sizes()->attach($request->sizes);
           }
                   // }
        if($request->has('photo')){
           // Enregistrer les images
           foreach ($request->file('photo') as $image) {
            //$imagePath = $image->store('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
             $image->move(public_path('storage/products'), $imageName);
    
            // Créer une nouvelle image associée au produit
            $productImage = new Image();
            $productImage->product_id = $product->id;
            $productImage->path = asset('/storage/products'). '/'  . $imageName;
            $productImage->save();
        }}
      
            $store = new Store();
            $store->quantity = $product->quantity;
            $store->price =$product->priceSale;
            $store->product_id = $product->id;
            $store->instagrammer_id = $product->instagrammer_id;
            $store->save();
        
            return response()->json([
                'message' => 'Product created!',
                "status" => Response::HTTP_CREATED,
                "data" => new ProductResource($product)
            ]);
    }

    public function show($id)
    {
        $product = Product::find($id);
        return new ProductResource($product);    }
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'quantity' => 'numeric|nullable',
            'priceSale' => 'numeric|nullable',
            'priceFav' => 'numeric|nullable',
            'priceMax' => 'numeric|nullable',
            'brand' => 'string|nullable',
            'category' => ['in:CLOTHING,ACCESSOIRIES,HOME,SPORT,BEAUTY,ELECTRONICS,PETS', 'nullable'],
            'status' => ['in:INSTOCK,OUTSTOCK', 'nullable'],
            'echantillon' => ['in:FREE,PAID,REFUNDED', 'nullable'],
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'status' => 400
            ]);
        }
    
        $product = Product::findOrFail($id);
    
        // Mettre à jour uniquement les champs présents dans la requête
        if ($request->has('name')) {
            $product->name = $request->name;
        }
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        if ($request->has('quantity')) {
            $product->quantity = $request->quantity;
        }
        if ($request->has('priceSale')) {
            $product->priceSale = $request->priceSale;
        }
        if ($request->has('priceFav')) {
            $product->priceFav = $request->priceFav;
        }
        if ($request->has('priceMax')) {
            $product->priceMax = $request->priceMax;
        }
        if ($request->has('brand')) {
            $product->brand = $request->brand;
        }
        if ($request->has('category')) {
            $product->category = $request->category;
        }
        if ($request->has('status')) {
            $product->status = $request->status;
        }
        if ($request->has('echantillon')) {
            $product->echantillon = $request->echantillon;
        }
    
        $product->reference = Str::random(8);
        $product->save();
    
        if ($request->has('category')) {
            $subcategorie = SubCategory::where('type', $request->category)->first();
            if ($subcategorie) {
                $product->subcategories()->detach([$subcategorie->id]);
            } else {
                return response()->json([
                    'message' => 'SubCategory not found',
                    'status' => 404
                ]);
            }
        }
    
        if ($request->has('colors')) {
            $product->colors()->detach();
            $product->colors()->attach($request->colors);
        }
    
        if ($request->has('sizes')) {
            $product->sizes()->detach();
            $product->sizes()->attach($request->sizes);
        }
        
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $id) {
                $image = Image::find($id);
                $image->delete();
              
            }
        }
        if($request->has('photo')){
            // Enregistrer les images
               foreach ($request->file('photo') as $image) {
                $imagePath = $image->store('photo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                 $image->move(public_path('storage/products'), $imageName);
        
                // Créer une nouvelle image associée au produit
                $productImage = new Image();
                $productImage->product_id = $product->id;
                $productImage->path = asset('/storage/products'). '/' . $imageName;
                $productImage->save();
            }
        }
            
        
    
        return response()->json($product);
    }
    public function destroy($id)
    {
        $products = Product::find($id);
        $products->delete();
        return response()->json('Product deleted!');
    }
}

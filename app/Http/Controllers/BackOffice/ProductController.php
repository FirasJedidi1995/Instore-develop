<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }

    public function index()
    {
        $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])->get();
        return response()->json($products, Response::HTTP_OK);
    }

    public function indexPending()
{
    $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])
                        ->where('approval_status', 'pending')
                        ->get();

    return response()->json($products, Response::HTTP_OK);
}
public function indexApproved()
{
    $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])
                        ->where('approval_status', 'approved')
                        ->get();

    return response()->json($products, Response::HTTP_OK);
}

public function indexRefused()
{
    $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])
                        ->where('approval_status', 'refused')
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
        'subcategory_id' => 'required|exists:subcategories,id',
        'brand_id' => 'nullable|exists:brands,id',
        'echantillon' => 'nullable|in:FREE,PAID,REFUNDED',
        'combinations' => 'nullable|array',
        'combinations.*.size' => 'nullable|string|max:255',
        'combinations.*.color' => 'nullable|string|max:255',
        'combinations.*.quantity' => 'required_with:combinations|integer|min:0',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|max:2048',
    ];

    $validatedData = $request->validate($rules);

    // if (isset($validatedData['priceFav']) && $validatedData['priceFav'] <= $validatedData['priceSale']) {
    //     return response()->json(['error' => 'Le prix favori doit être supérieur au prix de vente.'], 422);
    // }

    // if (isset($validatedData['priceMax']) && $validatedData['priceMax'] <= $validatedData['priceFav']) {
    //     return response()->json(['error' => 'Le prix maximum doit être supérieur au prix favori.'], 422);
    // }

    $user = auth()->user();
    $reference = Str::random(8);

    $product = new Product();
    $product->name = $validatedData['name'];
    $product->description = $validatedData['description'] ?? null;
    $product->quantity = $validatedData['quantity'] ?? 0;
    $product->priceSale = $validatedData['priceSale'];
    $product->reference = $reference;
    $product->subcategory_id = $validatedData['subcategory_id'];
    $product->brand_id = $validatedData['brand_id'] ?? null;
    $product->echantillon = $validatedData['echantillon'] ?? null;
    $product->admin_id = $user->id;

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

    public function show($id)
    {
        $product = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product, Response::HTTP_OK);                 
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0|required_without_all:combinations',
            'priceSale' => 'sometimes|required|numeric|min:0',
            'subcategory_id' => 'sometimes|required|exists:subcategories,id',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'echantillon' => 'nullable|in:FREE,PAID,REFUNDED',
            'combinations' => 'nullable|array',
            'combinations.*.size' => 'nullable|string|max:255',
            'combinations.*.color' => 'nullable|string|max:255',
            'combinations.*.quantity' => 'required_with:combinations|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
            //'status' => ['in:INSTOCK,OUTSTOCK', 'nullable'],
        ];
        
        $validatedData = $request->validate($rules);
        //dd($request->getContent());
        $product->name = array_key_exists('name', $validatedData) ? $validatedData['name'] : $product->name;
        $product->description = $validatedData['description'] ?? null;
        $product->priceSale = $validatedData['priceSale'];
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
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }

    public function setFinalPrices(Request $request, $ProductId)
{
    // Validation des données d'entrée
    $rules = [
        'gain' => 'required|numeric|min:1', 
    ];
    
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $product = Product::find($ProductId);

    if (!$product) {
        return response()->json(['error' => 'Produit non trouvé'], 404);
    }

    
    $gain = $request->input('gain');
    $product->priceFav = $product->priceSale * (1 + $gain / 100);

    
    $product->priceMax = $product->priceFav * 1.5;

    $product->save();

    return response()->json(['message' => 'Prix final mis à jour avec succès', 'product' => $product], 200);
}
public function changeApprovalStatus(Request $request, $id)
{
    
    $rules = [
        'approval_status' => 'required|in:pending,approved,refused',
    ];
    
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['error' => 'Produit non trouvé'], 404);
    }

    
    $product->approval_status = $request->input('approval_status');
    $product->save();

    return response()->json(['message' => 'Statut d\'approbation mis à jour avec succès', 'product' => $product], 200);
}


}
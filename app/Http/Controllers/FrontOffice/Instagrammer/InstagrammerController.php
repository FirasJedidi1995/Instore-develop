<?php

namespace App\Http\Controllers\FrontOffice\Instagrammer;

use App\Jobs\MessageJob;
use App\Jobs\SendMessageProviderJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\EchantillonResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Echantillon;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class InstagrammerController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:provider-intern');

    }

    public function updateSelfData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'string',
                Rule::unique('users'),
                'email'
            ],
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'acountLink'=> 'nullable|string',
            'street'=> 'nullable|string',
            'city'=> 'nullable|string',
            'post_code'=> ['nullable', 'regex:/^[0-9]{4}$/'],
            'CIN'=> ['nullable', 'regex:/^[0-9]{8}$/'],
            'TAXNumber'=> 'nullable|regex:/^[0-9]{8}$/',
            'companyName'=> 'nullable|string',
            'companyUnderConstruction'=> 'nullable|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                "status" => 400
            ]);
        }
    
        $user = User::findOrFail(Auth::user()->id);
    
        if (is_null($user)) {
            return response()->json(
                [
                    'message' => 'utilisateur introuvable',
                    "status" => "404"
                ]
            );
        }
    
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->acountLink = $request->acountLink;
         $user->street = $request->street;
            $user->city = $request->city;
            $user->post_code = $request->post_code;
            $user->CIN = $request->CIN;
            $user->companyName = $request->companyName;
            $user->companyUnderConstruction = $request->companyUnderConstruction;
            if ($request->companyUnderConstruction == false) {
                $user->TAXNumber  = $request->TAXNumber;
            } 
     
    
        $user->save();
    
        return response()->json([
            "message" => "Updated Successfully",
            "status" => 200,
        ]);
    }
    public function getInstagrammerProducts()
    {
        $products = Product::where("instagrammer_id", "=", auth()->user()->id)->get();
        return response()->json([
            'message' => 'all Instagrammer products',
            "status" => Response::HTTP_OK,
            "data" =>  ProductResource::collection($products)
        ]);
       
    } 
    // public function getStoreProducts()
    // {
    //     $products = Store::where("instagrammer_id", "=", auth()->user()->id)->get();
    //     return response()->json([
    //         'message' => 'all Instagrammer products',
    //         "status" => Response::HTTP_OK,
    //         "data" =>  StoreResource::collection($products)
    //     ]);
       
    // } 
    public function getProviderProducts()
    {
        $products = Product::with(['subcategory', 'brand', 'sizes', 'colors', 'images'])->whereNotNull('provider_id')->get();
        return response()->json([
            'message' => 'All provider products',
            "status" => Response::HTTP_OK,
            "data" =>  $products
        ]);
    
        
    }

//     public function getProviderProducts()
// {
//     $products = Product::whereNotNull('provider_id')->get();
//     return response()->json([
//         'message' => 'All provider products',
//         "status" => Response::HTTP_OK,
//         "data" =>  ProductResource::collection($products)
//     ]);

    
// }

public function filterProducts(Request $request)
{
    // Récupération du paramètre de catégorie
    $category = $request->input('category');

    if (empty($category)) {
        return response()->json(['error' => 'Le paramètre de catégorie est obligatoire.'], 400);
    }

  
    $products = Product::where('category', $category)
                        ->whereNotNull('provider_id')
                        ->get();

    return response()->json($products);
}



    // public function addEchantillon(Request $request)
    // {
    //     $product = Product::find($request->product_id);

    //     if (!$product) {
    //         return response()->json([
    //             'message' => 'Product not found',
    //             'status' => Response::HTTP_NOT_FOUND
    //         ]);
    //     }
    //     $echantillon = new Echantillon();
    //     $echantillon->payment = $request->payment;
    //     $echantillon->status = "PENDING";
    //     $echantillon->product_id = $product->id;
    //     $echantillon->instagrammer_id = Auth::user()->id;
    //     $echantillon->provider_id  = $product->provider_id;

    //     $echantillon->save();

    //     return response()->json([
    //         'message' => "Successfully ",
    //         "status" => Response::HTTP_CREATED,
    //             "data" => new EchantillonResource($echantillon)
            
    //     ]);
    // }

    public function addProductProvider(Request $request){

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }
        if( $product->status == "INSTOCK"){
           // $product->quantity -= $request->quantity; 
            $product->save();
            $store = new Store();
            $store->quantity = $product->quantity;
            $store->price =$request->price;
            $store->product_id = $product->id;
            $store->instagrammer_id =  Auth::user()->id;
            $store->save();
            return response()->json([
                'message' => "Successfully ",
                "status" => Response::HTTP_CREATED
            ]); 
        } else {
            return response()->json([
                'message' => 'Product quantity is insufficient',
                'status' => Response::HTTP_BAD_REQUEST
            ]);
        }
    }

    public function sendProviderMessage(Request $request)
    {
        $rules = [
            'message' => 'required|string',
            // 'user_id' => 'required|exists:users,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $messages = new Message();
        $messages->message  = $request->message;
        $messages->receiver_id  = Auth::user()->id;
        $messages->sender_type = 'PROVIDER';
       
        $messages->save();
        dispatch(new SendMessageProviderJob($messages));
        return response()->json([
            'message' => 'Message created!',
            "status" => Response::HTTP_CREATED,
            "data" => new MessageResource($messages)
        ]);
    }

    public function createStore(Request $request)
    {
        $user = Auth::user();
        $user = User::find(Auth::id());
        //dd($user);

        // Valider les données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        
        // Gérer le logo si fourni
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('stores/logos'), $logoName);
            $validatedData['logo'] = asset('stores/logos/' . $logoName);
        }

       
        $store = $user->store()->create($validatedData);

        return response()->json(['store' => $store, 'message' => 'Store created successfully']);
    }

    public function deleteStore($id)
{
    $user = Auth::user();
    $user = User::find(Auth::id());
    $store = $user->store()->find($id);

    if (!$store) {
        return response()->json(['message' => 'Store not found or you are not authorized to delete this store'], 404);
    }

    
    if ($store->logo) {
        $logoPath = public_path(parse_url($store->logo, PHP_URL_PATH));
        if (file_exists($logoPath)) {
            unlink($logoPath);
        }
    }

    $store->delete();

    return response()->json(['message' => 'Store deleted successfully']);
}

public function getInstagrammerStore()
{
    $user = Auth::user();

   
    $store = $user->store;

    if (!$store) {
        return response()->json(['message' => 'No store found for this user'], 404);
    }

    return response()->json(['store' => $store]);
}

public function addProductToStore(Request $request, $productId)
{
    $user = Auth::user();

    
    $store = $user->store;

    if (!$store) {
        return response()->json(['message' => 'Aucun store trouvé pour cet utilisateur'], 404);
    }

    
    $product = Product::findOrFail($productId);

    
    if ($store->products()->where('product_id', $productId)->exists()) {
        return response()->json(['message' => 'Le produit est déjà présent dans ce store'], 409);
    }

    
    $salePrice = $request->input('sale_price');

    if ($salePrice < $product->priceSale || $salePrice < $product->priceFav || $salePrice > $product->priceMax) {
        return response()->json([
            'message' => 'Le prix de vente doit être supérieur à ' . $product->priceSale . ' et compris entre ' . $product->priceFav . ' et ' . $product->priceMax
        ], 422); 
    }

   
    $store->products()->attach($product, ['sale_price' => $salePrice]);

    return response()->json(['message' => 'Produit ajouté avec succès au store!']);
}



public function getStoreProducts()
    {

        $user = Auth::user();

    
        $store = $user->store;
    
        if (!$store) {
            return response()->json(['message' => 'No store found for this user'], 404);
        }
        $products = $store->products()->get();

        $response = $products->map(function ($product) {
            return [
                'product_id'=>$product->id,
                'product_name' => $product->name,
                'sale_price' => $product->pivot->sale_price,
                'quantity' => $product->quantity,
            ];
        });

        return response()->json($response);
    }


    public function removeProductFromStore( $productId)
{
    
    $user = Auth::user();

    $store = $user->store;

    

    
    $product = Product::findOrFail($productId);

   
    if (!$store->products()->where('product_id', $productId)->exists()) {
        return response()->json(['message' => 'Produit non trouvé dans ce store'], 404);
    }

    
    $store->products()->detach($product);

    return response()->json(['message' => 'Produit retiré avec succès du store!']);
}


public function updateProductInStore(Request $request, $productId)
{
    $user = Auth::user();

    
    $store = $user->store;

    if (!$store) {
        return response()->json(['message' => 'No store found for this user'], 404);
    }

    
    $product = Product::findOrFail($productId);

    
    if (!$store->products()->where('product_id', $productId)->exists()) {
        return response()->json(['message' => 'Produit non trouvé dans ce store'], 404);
    }

    
    $salePrice = $request->input('sale_price');

    
    if ($salePrice < $product->priceSale || $salePrice < $product->priceFav || $salePrice > $product->priceMax) {
        return response()->json([
            'message' => 'Le prix de vente doit être supérieur à ' . $product->priceSale . ' et compris entre ' . $product->priceFav . ' et ' . $product->priceMax
        ], 422);
    }

    
    $store->products()->updateExistingPivot($productId, ['sale_price' => $salePrice]);

    return response()->json(['message' => 'Prix de vente mis à jour avec succès!']);
}
public function getProductLink($productId)
{
    $user = Auth::user();
    $store = $user->store;

    if (!$store) {
        return response()->json(['message' => 'No store found for this user'], 404);
    }

    $product = $store->products()->where('product_id', $productId)->first();

    if (!$product) {
        return response()->json(['message' => 'Produit non trouvé dans ce store'], 404);
    }

    // Générer le lien
    $link = url('/store/' . $store->id . '/product/' . $product->id);

    return response()->json(['link' => $link]);
}

public function getOrders()
{
    $user = Auth::user();

    
    if (!$user->store) {
        return response()->json(['message' => 'L\'utilisateur n\'a pas de boutique associée.'], 404);
    }

    $storeId = $user->store->id; 

    // Récupérer les commandes pour la boutique associée
    $orders = Order::where('store_id', $storeId)->get();

    // Vérifier si des commandes existent
    if ($orders->isEmpty()) {
        return response()->json(['message' => 'Aucun ordre trouvé.'], 404);
    }

    return response()->json($orders);
}


    
}
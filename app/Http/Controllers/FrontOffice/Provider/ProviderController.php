<?php

namespace App\Http\Controllers\FrontOffice\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\EchantillonResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendMessageProviderJob;
use App\Models\Echantillon;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:provider-extern');

    }
    
    public function getProviderProducts()
    {
        $products = Product::where("provider_id", "=", auth()->user()->id)->get();
        return response()->json([
            'message' => 'List Products !',
            "status" => Response::HTTP_OK,
            "data" =>  ProductResource::collection($products)
        ]);
        
    } 
    // public function getEchantillon()
    // {
    //     // Récupérer l'utilisateur connecté
    //     $provider = Auth::user();

    //     // Récupérer les échantillons des produits du fournisseur connecté
    //     $echantillons = Echantillon::whereHas('product', function ($query) use ($provider) {
    //         $query->where('provider_id', $provider->id);
    //     })->get();

    //     return response()->json([
    //         'message' => 'List Echantillon!',
    //         'status' => Response::HTTP_OK,
    //         'data' => EchantillonResource::collection($echantillons)
    //     ]);
    // }

    // public function updateSelfData(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string',
    //         'email' => [
    //             'required',
    //             'string',
    //             Rule::unique('users'),
    //             'email'
    //         ],
    //         'phone' => ['required', 'regex:/^[0-9]{8}$/'],
    //         'street'=> 'nullable|string',
    //         'city'=> 'nullable|string',
    //         'post_code'=> ['nullable', 'regex:/^[0-9]{4}$/'],
    //         'CIN'=> ['nullable', 'regex:/^[0-9]{8}$/'],
    //         'TAXNumber'=> 'nullable|regex:/^[0-9]{8}$/',
    //         'companyName'=> 'nullable|string',
    //         'companyUnderConstruction'=> 'nullable|boolean',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'errors' => $validator->errors(),
    //             "status" => 400
    //         ]);
    //     }
    
    //     $user = User::findOrFail(Auth::user()->id);
    
    //     if (is_null($user)) {
    //         return response()->json(
    //             [
    //                 'message' => 'utilisateur introuvable',
    //                 "status" => "404"
    //             ]
    //         );
    //     }
    
    //     $user->name = $request->name;
    //     $user->phone = $request->phone;
    //     $user->email = $request->email;
    //     $user->street = $request->street;
    //     $user->city = $request->city;
    //     $user->post_code = $request->post_code;
    //     $user->CIN = $request->CIN;
    //     $user->companyName = $request->companyName;
    //     $user->companyUnderConstruction = $request->companyUnderConstruction;
    //     if ($request->companyUnderConstruction == false) {
    //             $user->TAXNumber  = $request->TAXNumber;
    //         } 
     
    
    //     $user->save();
    
    //     return response()->json([
    //         "message" => "Updated Successfully",
    //         "status" => 200,
    //     ]);
    // }

    // public function updateEchantillon(Request $request, $id)
    // {
    //     $echantillon = Echantillon::find($id);
    
    //     if (!$echantillon) {
    //         return response()->json([
    //             'message' => 'Echantillon not found',
    //             'status' => Response::HTTP_NOT_FOUND
    //         ]);
    //     }
    
    //     $echantillon->status = $request->status;
    //     $echantillon->save();
    
    //     return response()->json([
    //         'message' => "Echantillon status updated successfully",
    //         "status" => Response::HTTP_OK
    //     ]);
    // }
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
    public function getMessagesByAdmin()
    {
        $user = Auth::user();
        $messages = Message::where('receiver_id', $user->id)->get();
        return response()->json([
            'message' => 'List messages !',
            "status" => Response::HTTP_OK,
            "data" =>  MessageResource::collection($messages)
        ]);
       ; 
    }  
    //     public function getOrdersByProvider()
    // {
    //     // Supposons que l'utilisateur connecté est le fournisseur
    //     $providerId = Auth::id();

    //     // Récupérer les commandes dont les produits ont le provider_id correspondant
    //     $orders = Order::whereHas('product', function ($query) use ($providerId) {
    //         $query->where('provider_id', $providerId);
    //     })->with('product')->get();

    //     return response()->json([
    //         'message' => 'List Orders by Provider!',
    //         'status' => 200,
    //         'data' => $orders
    //     ]);
    // }

    // public function show($id)
    // {  
    //  $orders = Order::find($id);
    // return new OrderResource($orders); 
    // }

   
    // public function getListEchantillons()
    // {
    //     $echantillons = Echantillon::where('provider_id', Auth::user()->id)->get();
    //     return response()->json([
    //         'message' => 'Echantillons data !',
    //         "status" => Response::HTTP_OK,
    //         "data" =>  EchantillonResource::collection($echantillons)
    //     ]);
    //    ; 
    // }  

    // public function showEchantillon($id)
    // {  
    //  $echantillons = Echantillon::find($id);
    // return new EchantillonResource($echantillons); 
    // }

    public function getOrderByStatus(Request $request)
{
    // Récupérer le statut des paramètres de la requête
    $status = $request->input('status');

    // Vérifier si le statut est fourni, sinon récupérer toutes les commandes
    if ($status) {
        $orders = Order::where('status', $status)->get();
    } else {
        $orders = Order::all();
    }

    return response()->json([
        'message' => 'List Orders!',
        "status" => Response::HTTP_OK,
        "data" => OrderResource::collection($orders)
    ]);
}
}
<?php

namespace App\Http\Controllers;

use App\Models\Echantillon;
use App\Models\Product;
use Illuminate\Http\Request;

class EchantillonController extends Controller
{

    
    public function __construct()
    {
        $this->middleware('auth:api')->only('requestEchantillon', 'updateEchantillonStatus', 'getEchantillonsRequestForOwner');
    }

    public function getEchantillonsRequestForOwner()
    {
        $user = auth()->user();
    
        
        $echantillons = Echantillon::where('product_owner_id', $user->id)->get();
    
        return response()->json(['echantillons' => $echantillons]);
    }
    
    public function requestEchantillon($productId)
    {
        $user = auth()->user();

        $existingRequest = Echantillon::where('product_id', $productId)
        ->where('instagrammer_id', $user->id)
        ->first();


if ($existingRequest && $existingRequest->status !== 'REJECTED') {
return response()->json(['message' => 'You have already requested a sample for this product.'], 403);
}
        $product=Product::find($productId);
        $echantillon = Echantillon::create([
            'product_id' => $productId,
            'instagrammer_id' => $user->id,
            'product_owner_id'=>$product->admin_id?:$product->instagrammer_id?:$product->provider_id, 
            'status' => 'PENDING'
        ]);
        //dd($echantillon);
    
        return response()->json(['message' => 'Sample requested successfully', 'echantillon' => $echantillon]);
    }
    

public function updateEchantillonStatus(Request $request, $id)
    {
        $echantillon = Echantillon::find($id);
        if (!$echantillon) {
            return response()->json(['error' => 'Echantillon non trouvé'], 404);
        }

        $user = auth()->user();

        //$product = $echantillon->product;

       
        if($echantillon->product_owner_id !==$user->id){
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED'
        ]);

        $echantillon->status = $request->status;
        $echantillon->save();

        return response()->json(['message' => 'Statut mis à jour avec succès', 'echantillon' => $echantillon]);
    }


   
    
}
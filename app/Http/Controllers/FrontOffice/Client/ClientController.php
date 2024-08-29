<?php

namespace App\Http\Controllers\FrontOffice\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Mail\OrderConfirmation;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\Store;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; // Import the File facade


class ClientController extends Controller
{
    public function getProductById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        return new ProductResource($product);
    }

    public function getOrderById($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        return new OrderResource($order);
    }

    public function addOrder(Request $request, $storeId, $productId)
{
    // Récupérer le produit et vérifier s'il a des tailles ou des couleurs
    $store = Store::find($storeId);
    $product = $store->products()->where('product_id', $productId)->firstOrFail();

    $hasSizes = $product->sizes()->exists();
    $hasColors = $product->colors()->exists();

    // Valider les données de la requête
    $request->validate([
        'quantity' => 'required|integer|min:1',
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'city' => 'required|string|max:255',
        'street' => 'required|string|max:255',
        'post_code' => 'required|integer',
        'payment' => 'required|in:Credit,CashOnDelivery',
        'color_id' => $hasColors ? 'required|exists:colors,id' : 'nullable',
        'size_id' => $hasSizes ? 'required|exists:sizes,id' : 'nullable',
        'cardNumber' => 'required_if:payment,Credit|digits_between:9,12',
        'securityCode' => 'required_if:payment,Credit|digits:4',
        'CVV' => 'required_if:payment,Credit|digits:3',
    ]);

   

    // Vérification de la disponibilité de la quantité
    if ($hasSizes || $hasColors) {
        $productSizeColor = $product->sizes()
            ->wherePivot('color_id', $request->input('color_id'))
            ->where('size_id', $request->input('size_id'))
            ->first();

        if (!$productSizeColor || $productSizeColor->pivot->quantity < $request->input('quantity')) {
            return response()->json(['message' => 'La quantité demandée pour cette combinaison taille/couleur n\'est pas disponible'], 400);
        }

        // Mise à jour de la quantité disponible
        $productSizeColor->pivot->quantity -= $request->input('quantity');
        
        $productSizeColor->pivot->save();
    } else {
        if ($product->quantity < $request->input('quantity')) {
            return response()->json(['message' => 'La quantité demandée pour ce produit n\'est pas disponible'], 400);
        }

        // Mise à jour de la quantité disponible
        $product->quantity -= $request->input('quantity');
        
    }
    $totalPrice = $product->pivot->sale_price * $request->input('quantity');

    // Création de la commande
    $order = Order::create([
        'firstName' => $request->input('firstName'),
        'lastName' => $request->input('lastName'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'city' => $request->input('city'),
        'street' => $request->input('street'),
        'post_code' => $request->input('post_code'),
        'reference' => Str::random(8),
        'quantity' => $request->input('quantity'),
        'totalPrice' => $totalPrice,
        'payment' => $request->input('payment'),
        'status' => 'PENDING',
        'product_id' => $productId,
        'store_id' => $storeId,
        'color_id' => $hasColors ? $request->input('color_id') : null,
        'size_id' => $hasSizes ? $request->input('size_id') : null,
        'cardNumber' => $request->has('cardNumber') ? encrypt($request->cardNumber) : null,
        'securityCode' => $request->has('securityCode') ? encrypt($request->securityCode) : null,
        'CVV' => $request->has('CVV') ? encrypt($request->CVV) : null,
    ]);

    // Sauvegarder le produit
    $product->save();

    // Envoi de l'email de confirmation
    Mail::to($request->email)->send(new OrderConfirmation($order));

    // Génération du fichier PDF
    $OrderProduct = [
        'order' => $order,
        'product' => Product::find($order->product_id),
    ];
    $pdf = Pdf::loadView('invoice', compact('OrderProduct'));

    $fileName = $order->id . '_invoice.pdf';
    $directoryPath = public_path('invoices');
    if (!File::exists($directoryPath)) {
        File::makeDirectory($directoryPath, 0755, true);
    }
    $filePath = $directoryPath . '/' . $fileName;

    // Sauvegarde du fichier PDF
    $pdf->save($filePath);
    $order->update(['invoice_link' => $filePath]);

    return response()->json([
        'message' => 'Order created!',
        "status" => Response::HTTP_CREATED,
        "data" => $order
    ]);
}


    // public function addOrder(Request $request,$storeId,$productId)
    // {
    //     // dd($storeId);
    //     $request->validate([
    //         'quantity' => 'required|integer|min:1',
    //         'firstName' => 'required|string|max:255',
    //         'lastName' => 'required|string|max:255',
    //         'email' => 'required|email|max:255',
    //         'phone' => 'required|string|max:20',
    //         'city' => 'required|string|max:255',
    //         'street' => 'required|string|max:255',
    //         'post_code' => 'required|integer',
    //         'payment' => 'required|in:Credit,CashOnDelivery',
    //         'color_id' => 'required|exists:colors,id',
    //         'size_id' => 'required|exists:sizes,id',
    //         'cardNumber' => 'required_if:payment,Credit|digits_between:9,12', // Card number validation
    //         'securityCode' => 'required_if:payment,Credit|digits:4',
    //         'CVV'=>'required_if:payment,Credit|digits:3'
            
    //     ]);

        
    //     //  dd($request);
    //     $store= Store::find($storeId);
    //     $product=$store->products()->where('product_id',$productId)->firstOrFail();
    //     //dd($store);

        
    //     $productSizeColor = $product->sizes()
    //     ->wherePivot('color_id', $request->input('color_id'))
    //     ->where('size_id', $request->input('size_id'))
    //     ->first();
    

    //     //dd( $productSizeColor->pivot->quantity );
    //     if (!$productSizeColor || $productSizeColor->pivot->quantity < $request->input('quantity')) {
    //         return response()->json(['message' => 'La quantité demandée pour cette combinaison taille/couleur n\'est pas disponible'], 400);
    //     }

    //     //dd($product->pivot->sale_price);
    //     $totalPrice = $product->pivot->sale_price * $request->input('quantity');
    //     //dd($productSizeColor->pivot->color_id);
        
    //     $order = Order::create([
    //         'firstName' => $request->input('firstName'),
    //         'lastName' => $request->input('lastName'),
    //         'email' => $request->input('email'),
    //         'phone' => $request->input('phone'),
    //         'city' => $request->input('city'),
    //         'street' => $request->input('street'),
    //         'post_code' => $request->input('post_code'),
    //         'reference' => Str::random(8),
    //         'quantity' => $request->input('quantity'),
    //         'totalPrice' => $totalPrice,
    //         'payment' => $request->input('payment'),
    //         'status' => 'PENDING',
    //         'product_id' => $productId,
    //         'store_id' => $storeId,
    //         'color_id' => $productSizeColor->pivot->color_id,
    //         'size_id' => $productSizeColor->pivot->size_id,
    //         'cardNumber' => encrypt($request->cardNumber),
    //         'securityCode' => encrypt($request->securityCode),
    //         'CVV' => encrypt($request->CVV),
    //     ]);

    //     // Mettre à jour la quantité disponible
    //     $productSizeColor->pivot->quantity -= $request->input('quantity');
    //     $product->quantity-=$request->input('quantity');
    //     $productSizeColor->pivot->save();
    //     $product->save();
    //     $OrderProduct = [
    //         'order' =>$order,
    //         'product' => Product::find($order->product_id)
    //      ];

    //     Mail::to($request->email)->send(new OrderConfirmation($order));
    //    // dd($OrderProduct);
    //    $pdf = Pdf::loadView('invoice', compact('OrderProduct'));

    //     $fileName = $order->id . '_invoice.pdf';
    //     $directoryPath = public_path('invoices');

    //     // Create the directory if it doesn't exist
    //     if (!File::exists($directoryPath)) {
    //         File::makeDirectory($directoryPath, 0755, true);
    //     }

    //     $filePath = $directoryPath . '/' . $fileName;

    //     // Sauvegarder le fichier PDF
    //     $pdf->save($filePath);
    //     $order->update(['invoice_link' => $filePath]);
    //      return response()->json([
    //         'message' => 'Order created!',
    //         "status" => Response::HTTP_CREATED,
    //         "data" =>$order
    //     ]);
    // }


    public function cancelOrder($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    $order->status = 'CANCEL';
    $order->save();

    return response()->json(['message' => 'Order cancled'], 200);
}

public function confirmOrder( $id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    $order->status = 'SUCCESS';
    $order->save();

    return response()->json(['message' => 'Order delivered'], 200);
}

    
}
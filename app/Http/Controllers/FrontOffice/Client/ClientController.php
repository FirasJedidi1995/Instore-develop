<?php

namespace App\Http\Controllers\FrontOffice\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use Barryvdh\DomPDF\Facade\Pdf;


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

    public function addOrder(Request $request)
    {
        $rules = [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'city' => 'required|string',
            'street' => 'required|string',
            'post_code' => ['required', 'regex:/^[0-9]{4}$/'],
            'cardNumber' => 'nullable|numeric',
            'securityCode' => ['nullable', 'regex:/^[0-9]{4}$/'],
            'CVV' => 'nullable|numeric',
            'quantity' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $remainingQuantity = Product::where('id', $request->input('product_id'))
                        ->value('quantity');
                    if ($value > $remainingQuantity) {
                        $fail($attribute . ' must be less than ' . $remainingQuantity);
                    }
                },
            ],
            'payment' => ['required', 'in:Credit,CashOnDelivery'],
            //'status' => ['required', 'in:SUCCESS,REFUSED,PENDING,CANCEL,INPROGRESS'],
            "product_id" => "required|exists:products,id",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $product = Product::findOrFail($request->product_id);
        $totalProduct = $request->quantity * $product->priceSale;

        // Calculer la TVA comme 19% du prix du produit
        $TVA = $product->priceSale * 0.19;
        $totalPrice = $totalProduct+$TVA;

        // Ajouter le shippingCost de 6 au totalPrice
         $totalPrice += 6;
       
        $orders = new Order();
        $orders->firstName  = $request->firstName;
        $orders->lastName  = $request->lastName;
        $orders->email  = $request->email;
        $orders->reference = str::random(8);
        $orders->city  = $request->city;
        $orders->street  = $request->street;
        $orders->color  = $request->color;
        $orders->size  = $request->size;
        $orders->phone  = $request->phone;
        $orders->city  = $request->city;
        $orders->post_code  = $request->post_code;
        $orders->cardNumber  = $request->cardNumber;
        $orders->securityCode  = $request->securityCode;
        $orders->CVV  = $request->CVV;
        $orders->quantity  = $request->quantity;
        $orders->shippingCost  = 6;
        $orders->TVA = 19;
        $orders->payment  = $request->payment;
        $orders->totalProduct  = $totalProduct;
        $orders->totalPrice  = $totalPrice;
        //$orders->status  = $request->status;
        $orders->product_id  = $request->product_id;
        $orders->save();

         // Mise à jour de la quantité du produit
         $product->quantity -= $request->quantity;
         $product->save();
         $OrderProduct = [
            'order' =>$orders,
            'product' => Product::find($orders->product_id)
         ];
         Mail::to($request->email)->send(new OrderConfirmation($orders));
         $pdf = Pdf::loadView('invoice', compact('OrderProduct'));

    // Définir le chemin de stockage
    $fileName = $orders->id . '_invoice.pdf';
    $filePath = public_path('storage/invoices/' . $fileName);

    // Sauvegarder le fichier PDF
    $pdf->save($filePath);

    // Mettre à jour le lien de la facture dans la commande
    $orders->invoice_link = asset('storage/invoices/' . $fileName);
    $orders->save();

   
         return response()->json([
            'message' => 'Order created!',
            "status" => Response::HTTP_CREATED,
            "data" => new OrderResource($orders)
        ]);
    }
    public function show($id)
    {
        $orders = Order::find($id);
        return response()->json($orders);
    }
    public function updateOrder(Request $request, $id)
    {
        $rules = [
            'firstName' => 'string',
            'lastName' => 'string',
            'email' => 'email',
            'phone' => ['regex:/^[0-9]{8}$/'],
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'city' => 'string',
            'street' => 'string',
            'post_code' => ['regex:/^[0-9]{4}$/'],
            'cardNumber' => 'nullable|numeric',
            'securityCode' => ['nullable', 'regex:/^[0-9]{4}$/'],
            'CVV' => 'nullable|numeric',
            'quantity' => [
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $remainingQuantity = Product::where('id', $request->input('product_id'))
                        ->value('quantity');
                    if ($value > $remainingQuantity) {
                        $fail($attribute . ' must be less than ' . $remainingQuantity);
                    }
                },
            ],
            'payment' => ['in:Credit,CashOnDelivery'],
            "product_id" => "exists:products,id",
            //'status' => ['in:SUCCESS,REFUSED,PENDING,CANCEL,INPROGRESS'],
        ];
           $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        // Trouver la commande à mettre à jour
        $orders = Order::findOrFail($id);

        // Vérifier si la commande existe
        if (!$orders) {
            return response()->json(['message' => 'order non trouvée'], 404);
        }
        $product = Product::findOrFail($request->product_id);
        $totalPrice = $request->quantity * $product->priceSale;

        // Calculer la TVA comme 7% du prix du produit
        $TVA = $product->priceSale * 0.07;
        $totalPrice += $TVA;

        // Ajouter le shippingCost de 6 au totalPrice
        $totalPrice += 6;
        $quantityDifference = $request->quantity - $orders->quantity;

        $orders->firstName  = $request->firstName;
        $orders->lastName  = $request->lastName;
        $orders->email  = $request->email;
        $orders->phone  = $request->phone;
        $orders->color  = $request->color;
        $orders->size  = $request->size;
        $orders->city  = $request->city;
        $orders->street  = $request->street;
        $orders->post_code  = $request->post_code;
        $orders->cardNumber  = $request->cardNumber;
        $orders->securityCode  = $request->securityCode;
        $orders->CVV  = $request->CVV;
        $orders->quantity  = $request->quantity;
        $orders->shippingCost  = 6;
        $orders->TVA = $TVA;
        $orders->payment  = $request->payment;
        $orders->product_id  = $request->product_id;
        $orders->totalPrice  = $totalPrice;

        $orders->save();

         // Mise à jour de la quantité du produit
         $product->quantity -= $quantityDifference;
         $product->save();
        // Mettre à jour les champs de la commande
        $orders->save();
        $OrderProduct = [
            'order' =>$orders,
            'product' => Product::find($orders->product_id)
         ];
         Mail::to($request->email)->send(new OrderConfirmation($orders));
         $pdf = Pdf::loadView('invoice', compact('OrderProduct'));

    // Définir le chemin de stockage
    $fileName = $orders->id . '_invoice.pdf';
    $filePath = public_path('storage/invoices/' . $fileName);

    // Sauvegarder le fichier PDF
    $pdf->save($filePath);

    // Mettre à jour le lien de la facture dans la commande
    $orders->invoice_link = asset('storage/invoices/' . $fileName);
    $orders->save();

        // Retourner la commande mise à jour
        return response()->json($orders);
    }
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
// public function sendEmail(Request $request){
//     // If email does not exist
//     if(!$this->validEmail($request->email)) {
//         return response()->json([
//             'message' => 'Email does not exist.'
//         ], Response::HTTP_NOT_FOUND);
//     } else {
//         // If email exists
//         $this->sendMail($request->email);
//         return response()->json([
//             'message' => 'Check your inbox, we have sent a link to reset email.'
//         ], Response::HTTP_OK);            
//     }
// }
    
}

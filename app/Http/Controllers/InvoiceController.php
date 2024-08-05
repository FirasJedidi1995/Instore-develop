<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
//     public function generateInvoice($orderId)
//     {
//         $order = Order::with('invoice')->findOrFail($orderId);

//         // Générer le PDF de la facture
//         $pdf = Pdf::loadView('invoice', compact('order'))->save(public_path(('storage/invoices') . $order->id . '_invoice.pdf'));

//         // Définir le chemin de stockage
//         // $fileName = 'invoices/' . $order->id . '_invoice.pdf';
//         // Storage::put('public/' . $fileName, $pdf->output());


// // // Définir le chemin de stockage
// // $filePath = public_path('storage/invoices/' . $fileName);
// // if (!File::exists(public_path('storage/invoices'))) {
// //     File::makeDirectory(public_path('storage/invoices'), 0755, true);
// // }
// // file_put_contents($filePath, $pdf->output());
// // $order->invoice_link = asset('storage/invoices/' . $fileName);


//         // Mettre à jour le lien de la facture dans la commande
//         $order->invoice_link =asset('storage/invoices/' . $pdf);
//         $order->save();

//         return $pdf->download('invoice.pdf');
//     }

public function generateInvoice($orderId)
{
    $order = Order::with('invoice')->findOrFail($orderId);

    // Générer le PDF de la facture
    $pdf = Pdf::loadView('invoice', compact('order'));

    // Définir le chemin de stockage
    $fileName = $order->id . '_invoice.pdf';
    $filePath = public_path('storage/invoices/' . $fileName);

    // Sauvegarder le fichier PDF
    $pdf->save($filePath);

    // Mettre à jour le lien de la facture dans la commande
    $order->invoice_link = asset('storage/invoices/' . $fileName);
    $order->save();

    return $pdf->download('invoice.pdf');
}
}

<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        /* Styles de la facture */
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .right-aligned {
            margin-left: auto;
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="5">
                    <table>
                        <tr>
                            <td class="title">
                                <h1>Facture</h1>
                            </td>
                            <td>
                                Reference #: {{ $OrderProduct['order']->reference }}<br>
                                Created: {{ $OrderProduct['order']->created_at->format('d/m/Y') }}<br>
                                Due: {{ $OrderProduct['order']->created_at->addDays(2)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="5">
                    <table>
                        <tr>
                            <td>
                                {{ $OrderProduct['order']->firstName }} {{ $OrderProduct['order']->lastName }}<br>
                                {{ $OrderProduct['order']->street }}, {{ $OrderProduct['order']->city }} {{ $OrderProduct['order']->post_code }}<br>
                                {{ $OrderProduct['order']->email }}, {{ $OrderProduct['order']->phone }}
                            </td>
                            <td class="right-aligned">
                                Company Name<br>
                                Street Address<br>
                                City, State, Zip Code<br>
                                Email
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Methode de paiement</td>
                <td>Check #</td>
            </tr>
            <tr class="details">
                <td>{{ $OrderProduct['order']->payment }}</td>
                <td>{{ $OrderProduct['order']->cardNumber }}</td>
            </tr>
            <tr class="heading">
                <td>Produit</td>
                <td>Couleur</td>
                <td>Taille</td>
                <td>Quantité</td>
                <td>Prix</td>
            </tr>
            <tr class="item">
                <td>{{ $OrderProduct['product']->name }}</td>
                <td>{{ $OrderProduct['order']->color }}</td>
                <td>{{ $OrderProduct['order']->size }}</td>
                <td>{{ $OrderProduct['order']->quantity }}</td>
                <td>{{ $OrderProduct['order']->totalProduct }}</td>
            </tr>
            <tr class="total">
                <td colspan="4"></td>
                <td>
                    <table>
                        <tr>
                            <td>TVA:</td>
                            <td>19%</td>
                        </tr>
                        <tr>
                            <td>Frais de livraison:</td>
                            <td>{{ $OrderProduct['order']->shippingCost }}</td>
                        </tr>
                        <tr>
                            <td>Total:</td>
                            <td>{{ $OrderProduct['order']->totalPrice }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
@component('mail::message')
# Confirmation de commande

Bonjour {{ $order->firstName }}{{ $order->lastName }},

Merci pour votre commande ! Nous sommes ravis de vous informer que votre commande a été reçue et est en cours de traitement. Voici les détails de votre commande :

**Numéro de commande :** {{ $order->reference }}

**Date de commande :** {{ $order->created_at->format('d/m/Y')}}

**Adresse de livraison :**  
{{ $order->city }},{{ $order->street }},{{ $order->post_code }}






**Total :** {{ $order->totalPrice }} DT

Pour suivre l'état de votre commande, veuillez cliquer sur le bouton ci-dessous :

@component('mail::button', ['url' => 'http://localhost:4200/order_tracking/' . $order->id])
Suivi de Commande
@endcomponent

Si vous avez des questions ou des préoccupations, n'hésitez pas à nous contacter.

Merci encore pour votre achat !

Cordialement,  


@endcomponent
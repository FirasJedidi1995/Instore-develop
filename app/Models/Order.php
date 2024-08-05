<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'firstName', 
        'secondName',
        'email',
        'phone',
        'city',
        'street',
        'post_code',
        'cardNumber',
        'securityCode',
        'CVV',
        'quantity',
        'TVA',
        'shippingCost',
        'payment',
        'totalPrice',
        'status',
        'product_id',
        'invoice_link'
    ];  
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}


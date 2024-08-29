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
        'lastName',
        'email',
        'phone',
        'city',
        'street',
        'post_code',
        'cardNumber',
        'securityCode',
        'CVV',
        'quantity',
        'reference',
        'payment',
        'totalPrice',
        'status',
        'product_id',
        'color_id',
        'size_id',
        'store_id',
        'invoice_link'
    ];  
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected  $fillable=['name', 'description', 'logo', 'instagrammer_id'];

    public function instagrammer()
    {
        return $this->belongsTo(User::class, 'instagrammer_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('sale_price');
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
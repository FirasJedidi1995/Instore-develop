<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
    use HasFactory;
 protected $fillable=['quantity'];
   

    public function images()
    {
        return $this->hasMany(ImagesProduct::class);
    }
    public function subcategory()
        {
            return $this->belongsTo(SubCategory::class);
        }
        
        public function brand(){
            return $this->belongsTo(Brand::class);
        }
    
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size_color')
                    ->withPivot('color_id', 'quantity')
                    ->withTimestamps();
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_size_color')
                    ->withPivot('size_id', 'quantity')
                    ->withTimestamps();
    }

    public function echantillons()
    {
        return $this->hasMany(Echantillon::class);
    }

    public function instagrammer()
    {
        return $this->belongsTo(User::class, 'instagrammer_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class)->withPivot('sale_price');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    } 
}
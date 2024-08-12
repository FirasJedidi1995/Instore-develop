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
}
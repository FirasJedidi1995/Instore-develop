<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
    use HasFactory;

    public function subcategory()
        {
            return $this->belongsTo(SubCategory::class);
        }
        
        public function brand(){
            return $this->belongsTo(Brand::class);
        }
          public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_color')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
    //     public function caracteristiques()
    // {
    //     return $this->hasMany(Caracteristique::class);
    // }
    // public function colors(){
    //     return $this->belongsToMany(Color::class,'caracteristiques')
    //     ->withPivot('size_id','quantity')->withTimestamps();
        
    // } 
    
    // public function sizes()
    // {
    //     return $this->belongsToMany(Size::class, 'caracteristiques')
    //                 ->withPivot('color_id', 'quantity')
    //                 ->withTimestamps();
    // }


  
}
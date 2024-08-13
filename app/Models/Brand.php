<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable=['name','image'];

    // public function categories(){
    //     return $this->belongsToMany(Category::class,'category_brand');
    // }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class,'brand_category');
    }
}
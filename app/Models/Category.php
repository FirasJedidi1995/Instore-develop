<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable=['name'];

    public function subcategories(){
        return $this->hasMany(subCategory::class);
    }
    public function brands(){
        return $this->belongsToMany(Brand::class,'brand_category');
    }
}
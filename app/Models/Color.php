<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    
    protected $table = 'colors';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];


    // public function caracteristiques()
    // {
    //     return $this->hasMany(Caracteristique::class);
    // }

    
    // public function products()
    // {
    //     return $this->belongsToMany(Product::class,'color_product');
    // }

    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'caracteristiques')
    //                 ->withPivot('size_id', 'quantity')
    //                 ->withTimestamps();
    // }


    public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
    
}
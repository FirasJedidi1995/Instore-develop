<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $table = 'sizes';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    // public function caracteristiques()
    // {
    //     return $this->hasMany(Caracteristique::class);
    // }

    // public function products()
    // {
    //     return $this->belongsToMany(Product::class);
    // }
    
    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'caracteristiques')
    //                 ->withPivot('color_id', 'quantity')
    //                 ->withTimestamps();
    // }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echantillon extends Model
{
    use HasFactory;
    protected $table = 'echantillon';
    protected $primaryKey = 'id';
    protected $fillable = ['instagrammer_id','product_id','status','product_owner_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function instagrammer()
    {
        return $this->belongsTo(User::class, 'instagrammer_id');
    }
    
    


    
}
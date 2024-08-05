<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verification_code extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = [
        'email',
        'code',
        'status'
    ];

    protected $hidden = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}

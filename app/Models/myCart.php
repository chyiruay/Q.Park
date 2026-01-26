<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class myCart extends Model
{
    use HasFactory;
    protected $fillable=['orderID','userID','space','quantity','productID'];

    public function product(){
        return $this->belongsTo('App\Product');
    }

    public function user(){
        return $this->belongTo('App\User');
    }
}

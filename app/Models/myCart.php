<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class myCart extends Model
{
    use HasFactory;
    protected $fillable=['orderID','userID','date','startTime','space','quantity','productID'];

    //'productID'/'userID', not Laravel's *_id, so the keys have to be named explicitly
    public function product(){
        return $this->belongsTo(Product::class, 'productID');
    }

    public function user(){
        return $this->belongsTo(User::class, 'userID');
    }
}

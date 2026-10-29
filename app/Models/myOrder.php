<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class myOrder extends Model
{
    use HasFactory;
    protected $fillable=['paymentStatus','userID','amount'];

    //'userID', not Laravel's user_id, so the key has to be named explicitly
    public function user(){
        return $this->belongsTo(User::class, 'userID');
    }
}

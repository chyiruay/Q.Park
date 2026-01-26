<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable=['name','price','image','floorPlan','categoryID'];

    public function category(){

        return $this->belongsTo('App\Models\Category');

    }
}

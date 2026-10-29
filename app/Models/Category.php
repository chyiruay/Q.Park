<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable=['name'];

    //'categoryID', not Laravel's category_id, so the key has to be named explicitly
    public function product(){

        return $this->hasMany(Product::class, 'categoryID');

    }
}
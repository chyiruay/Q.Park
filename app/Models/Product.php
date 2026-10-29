<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable=['name','price','image','floorPlan','categoryID'];

    /**
     * Bays offered at every car park. One list for the booking dropdown and the
     * server-side check, so a hand-edited <option> can't reach the database.
     * Clashes are caught separately by CartController::isTaken().
     *
     * shortcut: the same 36 bays for every car park. Upgrade to a spaces table with
     * a productID when car parks need their own layouts.
     */
    public static function spaces(): array
    {
        return array_map(fn ($n) => 'A'.$n, range(1, 36));
    }

    //'categoryID', not Laravel's category_id, so the key has to be named explicitly
    public function category(){

        return $this->belongsTo(Category::class, 'categoryID');

    }
}

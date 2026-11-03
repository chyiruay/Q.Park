<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;
    protected $fillable=['name'];

    private const CACHE_KEY='categories.all';

    /**
     * Invalidate on any write, wherever it comes from. Doing this with model events
     * rather than a Cache::forget() in each controller action means a future write
     * path can't forget to.
     */
    protected static function booted(): void
    {
        static::saved(fn()=>static::flushCache());
        static::deleted(fn()=>static::flushCache());
    }

    /**
     * The category list as the car park forms need it. Cached because it changes
     * maybe monthly but is read on the booking page. Availability is deliberately
     * never cached — a stale bay list double-books a customer.
     *
     * The day-long TTL is a backstop, not the mechanism: the model events above are
     * what keep this correct, and the TTL only limits the damage if a write ever
     * bypasses Eloquent (a raw DB::table() update, or a hand-edited row).
     */
    public static function cached()
    {
        return Cache::remember(self::CACHE_KEY, now()->addDay(), fn()=>static::all());
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    //'categoryID', not Laravel's category_id, so the key has to be named explicitly
    public function product(){

        return $this->hasMany(Product::class, 'categoryID');

    }
}
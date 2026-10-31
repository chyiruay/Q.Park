<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class myCart extends Model
{
    use HasFactory;
    protected $fillable=['orderID','userID','date','startTime','space','quantity','productID'];

    /* How long an unpaid cart row holds its space. Longer than the 31-minute
       Stripe checkout window PaymentController opens, so a hold can't lapse
       while the customer is still on Stripe's page. */
    const HOLD_MINUTES=35;

    /**
     * Runs a check-then-write on one car park's bookings with that car park locked,
     * so two requests can't both find a space free and both take it. The lock is on
     * the products row because a free slot has no my_carts row to lock.
     */
    public static function whileLocked($productID, \Closure $fn)
    {
        return DB::transaction(function() use ($productID,$fn){
            DB::table('products')->where('id',$productID)->lockForUpdate()->first();
            return $fn();
        });
    }

    /**
     * Two bookings clash when each starts before the other ends. Paid rows always
     * count; unpaid rows only while their hold is fresh, so an abandoned cart
     * frees its space after HOLD_MINUTES.
     */
    public static function isTaken($productID, string $space, Carbon $start, int $hours, ?int $exceptId=null): bool
    {
        $end=$start->copy()->addHours($hours);

        //a booking may run past midnight, so look either side of the date too
        $rows=DB::table('my_carts')
            ->where('productID','=',$productID)
            ->where('space','=',$space)
            ->whereBetween('date',[$start->copy()->subDay()->toDateString(),$end->toDateString()])
            ->where(fn($q)=>$q->where('orderID','!=','')->orWhere('created_at','>=',now()->subMinutes(self::HOLD_MINUTES)))
            ->when($exceptId,fn($q)=>$q->where('id','!=',$exceptId))
            ->get(['date','startTime','quantity']);

        foreach($rows as $row){
            $rowStart=Carbon::parse($row->date.' '.$row->startTime);
            $rowEnd=$rowStart->copy()->addHours((int) $row->quantity);

            if($start->lt($rowEnd) && $rowStart->lt($end)){
                return true;
            }
        }

        return false;
    }

    //'productID'/'userID', not Laravel's *_id, so the keys have to be named explicitly
    public function product(){
        return $this->belongsTo(Product::class, 'productID');
    }

    public function user(){
        return $this->belongsTo(User::class, 'userID');
    }
}

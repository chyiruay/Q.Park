<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\myCart;
use Auth;
use Session;

class CartController extends Controller
{
    //auth now lives on the routes; the old _construct() typo meant it never ran

    public function add(Request $request){
        $booking=$request->validate([
            'id'=>'required|exists:products,id',
            'date'=>'required|date|after_or_equal:today',
            'time'=>'required|date_format:H:i',
            'quantity'=>'required|integer|min:1|max:24',
            'space'=>['required',Rule::in(Product::spaces())],
        ],[
            'id.exists'=>'That car park does not exist.',
            'space.in'=>'Pick a parking space from the list.',
            'time.date_format'=>'Enter a start time as HH:MM.',
        ],[
            //without these the customer is shown our column names
            'id'=>'car park',
            'date'=>'booking date',
            'time'=>'start time',
            'quantity'=>'hours',
            'space'=>'parking space',
        ]);

        $start=Carbon::parse($booking['date'].' '.$booking['time']);

        //the date rule allows today, so the time still has to be checked
        if($start->lte(now())){
            return back()->withInput()->withErrors(['time'=>'Pick a start time in the future.']);
        }

        if($this->isTaken($booking,$start)){
            return back()->withInput()->withErrors([
                'space'=>'Space '.$booking['space'].' is already booked for part of that time. Pick another space or time.',
            ]);
        }

        $addItem=myCart::create([
            'quantity'=>$booking['quantity'],
            'orderID'=>'',
            'date'=>$booking['date'],
            'startTime'=>$booking['time'],
            'space'=>$booking['space'],
            'productID'=>$booking['id'],
            'userID'=>Auth::id(),
        ]);
        Session::flash('success',"Booking added to your cart!");
        return redirect()->route('myCart');
    }

    /**
     * Two bookings clash when each starts before the other ends. Rows still sitting
     * in a cart count, so an abandoned cart holds its space.
     *
     * shortcut: no expiry on pending rows, so an abandoned cart blocks a space for
     * good. Upgrade when carts get a TTL or a release job.
     */
    private function isTaken(array $booking, Carbon $start): bool
    {
        $end=$start->copy()->addHours((int) $booking['quantity']);

        //a booking may run past midnight, so look either side of the date too
        $rows=DB::table('my_carts')
            ->where('productID','=',$booking['id'])
            ->where('space','=',$booking['space'])
            ->whereBetween('date',[$start->copy()->subDay()->toDateString(),$end->toDateString()])
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

    public function showMyCart(){
        $carts=DB::table('my_carts')
        ->leftjoin('products','products.id','=','my_carts.productID')
        ->select('my_carts.quantity as cartQty','my_carts.id as cid','my_carts.space as space','my_carts.date as bookDate','my_carts.startTime as bookTime','products.*')
        ->where('my_carts.orderID','=','')  //the item havent make payment
        ->where('my_carts.userID','=',Auth::id())
        ->get();

        //select my_carts.quantity as cartQty, my_carts.id as cid, products.* from my_carts left join products 
        //on products.id=my_carts.productID where my_cart.orderID='' and my_carts.userID='Auth::id()'
        
        return view('myCart')->with('carts',$carts);
    }

    public function delete($id){
        /* Scoped to the owner, otherwise any id deletes someone else's booking —
           and to rows still in the cart. Deleting a paid row cost the customer the
           booking they had paid for, left the order's total higher than the rows it
           listed, and freed the space for someone else to book. */
        $delete=myCart::where('id',$id)
            ->where('userID',Auth::id())
            ->where('orderID','')
            ->firstOrFail();
        $delete->delete();  //run SQL delete from my_Cart where id=$id
        return redirect()->route('myCart')->with('success','Booking removed from your cart.');
    }
    
}

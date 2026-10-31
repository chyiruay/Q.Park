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
        $booking=$this->validateBooking($request);

        $start=Carbon::parse($booking['date'].' '.$booking['time']);

        $error=myCart::whileLocked($booking['id'],function() use ($booking,$start){
            if($error=$this->bookingError($booking,$start)){
                return $error;
            }

            myCart::create([
                'quantity'=>$booking['quantity'],
                'orderID'=>'',
                'date'=>$booking['date'],
                'startTime'=>$booking['time'],
                'space'=>$booking['space'],
                'productID'=>$booking['id'],
                'userID'=>Auth::id(),
            ]);
            return null;
        });

        if($error){
            return back()->withInput()->withErrors($error);
        }

        Session::flash('success',"Booking added to your cart!");
        return redirect()->route('myCart');
    }

    /** The booking form again, filled in with a cart row's current values. */
    public function edit($id){
        $cart=$this->ownPendingRow($id);

        return view('order')->with('products',[Product::findOrFail($cart->productID)])
                            ->with('cart',$cart);
    }

    public function update(Request $request, $id){
        $cart=$this->ownPendingRow($id);

        //the car park stays fixed; changing it is a new booking, not an edit
        $request->merge(['id'=>$cart->productID]);
        $booking=$this->validateBooking($request);

        $start=Carbon::parse($booking['date'].' '.$booking['time']);

        $error=myCart::whileLocked($cart->productID,function() use ($booking,$start,$id){
            /* Read again under lock: checkout may have paid this row since the read
               above, and the edit must not rewrite a booking that's been paid for.
               recordPayment() locks the same row, so the two take turns. */
            $cart=$this->ownPendingRow($id,true);

            //the row being edited mustn't clash with its own old slot
            if($error=$this->bookingError($booking,$start,$cart->id)){
                return $error;
            }

            $cart->forceFill([
                'quantity'=>$booking['quantity'],
                'date'=>$booking['date'],
                'startTime'=>$booking['time'],
                'space'=>$booking['space'],
                'created_at'=>now(),   //the slot was just checked free, so it gets a fresh hold
            ])->save();
            return null;
        });

        if($error){
            return back()->withInput()->withErrors($error);
        }

        return redirect()->route('myCart')->with('success','Booking updated.');
    }

    /** Edits, like deletes, only reach your own rows that are still unpaid. */
    private function ownPendingRow($id, bool $lock=false): myCart
    {
        return myCart::where('id',$id)
            ->where('userID',Auth::id())
            ->where('orderID','')
            ->when($lock,fn($q)=>$q->lockForUpdate())
            ->firstOrFail();
    }

    /** Error for the form, or null when the slot can be booked. */
    private function bookingError(array $booking, Carbon $start, ?int $exceptId=null): ?array
    {
        //the date rule allows today, so the time still has to be checked
        if($start->lte(now())){
            return ['time'=>'Pick a start time in the future.'];
        }

        if(myCart::isTaken($booking['id'],$booking['space'],$start,(int) $booking['quantity'],$exceptId)){
            return ['space'=>'Space '.$booking['space'].' is already booked for part of that time. Pick another space or time.'];
        }

        return null;
    }

    private function validateBooking(Request $request): array
    {
        return $request->validate([
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
    }

    public function showMyCart(){
        $carts=DB::table('my_carts')
        ->leftjoin('products','products.id','=','my_carts.productID')
        ->select('my_carts.quantity as cartQty','my_carts.id as cid','my_carts.space as space','my_carts.date as bookDate','my_carts.startTime as bookTime','my_carts.created_at as heldAt','products.*')
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
        $delete=$this->ownPendingRow($id);
        $delete->delete();  //run SQL delete from my_Cart where id=$id
        return redirect()->route('myCart')->with('success','Booking removed from your cart.');
    }
    
}

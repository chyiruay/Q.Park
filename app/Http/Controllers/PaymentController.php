<?php

namespace App\Http\Controllers;

use Stripe;

use Illuminate\Http\Request;
use App\Models\myOrder;
use App\Models\myCart;
use Auth;
use DB;
use Session;
use Notification;
use PDF;

class PaymentController extends Controller
{
    //auth now lives on the routes; the old _construct() typo meant it never ran

     public function paymentPost(Request $request)
    {
        $request->validate([
            'cid'=>'required|array',
            'cid.*'=>'integer',
            'stripeToken'=>'required|string',
        ]);

        //price comes from the products table, never from the posted "sub" field
        $items=DB::table('my_carts')
            ->leftjoin('products','products.id','=','my_carts.productID')
            ->select('my_carts.id as cid','my_carts.quantity as cartQty','products.price')
            ->whereIn('my_carts.id',$request->input('cid'))
            ->where('my_carts.orderID','=','')
            ->where('my_carts.userID','=',Auth::id())
            ->get();

        if($items->isEmpty()){
            return back()->withErrors('Those bookings are no longer in your cart.');
        }

        $amount=$items->sum(fn($item)=>$item->price*$item->cartQty);

	    Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            Stripe\Charge::create ([
                    "amount" => (int) round($amount*100),
                    "currency" => "MYR",
                    "source" => $request->stripeToken,
                    "description" => "Testing payment",
            ]);

        $newOrder=myOrder::Create([
            'paymentStatus'=>'Done',    //check any return parameter from Stripe
            'userID'=>Auth::id(),
            'amount'=>$amount,
        ]);

        foreach($items as $item){
            $carts=myCart::find($item->cid);  //get the cart item record
            $carts->orderID=$newOrder->id;  //binding the orderID value with record
            $carts->save();
        }

        $email='tanchyiruay@gmail.com';
        Notification::route('mail',$email)->notify(new \App\Notifications\orderPaid($email));
        Session::flash('success','Booking successfully!');
        
        return back();
    }

    public function showOrder(){
        $orders=DB::table('my_orders')

        ->select('my_orders.id','my_orders.amount','my_orders.paymentStatus','my_orders.created_at')
        ->where('my_orders.userID','=',Auth::id())
        ->orderByDesc('my_orders.id')
        ->paginate(6);

        //the bookings each order paid for, keyed by order id
        $bookings=DB::table('my_carts')
        ->leftjoin('products','products.id','=','my_carts.productID')
        ->select('my_carts.orderID','my_carts.space','my_carts.date','my_carts.startTime','my_carts.quantity','products.name')
        ->whereIn('my_carts.orderID',$orders->pluck('id'))
        ->where('my_carts.userID','=',Auth::id())
        ->get()
        ->groupBy('orderID');

        return view('myOrder')->with(['orders'=>$orders,'bookings'=>$bookings]);
    }

    public function showAllOrder(){
        $orders=DB::table('my_orders')
        ->leftjoin('users','users.id','=','my_orders.userID')
        ->select('my_orders.id','my_orders.userID','my_orders.amount','my_orders.paymentStatus','my_orders.created_at','users.name','users.email')
        ->orderByDesc('my_orders.id')
        ->paginate(10);

        return view('showAllOrder')->with([
            'orders'=>$orders,
            'revenue'=>DB::table('my_orders')->sum('amount'),
        ]);
    }

    public function pdfReport(){
        $orders = DB::table('my_orders')
        ->select('my_orders.id','my_orders.userID','my_orders.amount','my_orders.created_at')
        ->get();
        $pdf=PDF::loadView('salesReport',compact('orders'));

        return $pdf->download('report.pdf');
    }
}

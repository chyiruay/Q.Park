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
    
    public function _construct(){
            $this->middleware('auth');
    }

     public function paymentPost(Request $request)
    {
      
	    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            Stripe\Charge::create ([
                    "amount" => $request->sub*100,
                    "currency" => "MYR",
                    "source" => $request->stripeToken,
                    "description" => "Testing payment",
            ]);

        $newOrder=myOrder::Create([
            'paymentStatus'=>'Done',    //check any return parameter from Stripe
            'userID'=>Auth::id(),
            'amount'=>$request->sub,
        ]);

        $orderID=DB::table('my_orders')->where('userID','=',Auth::id())->orderBy('created_at','desc')->first(); //get the order ID just now created
           
        $items=$request->input('cid');
        foreach($items as $item=>$value){
            $carts=myCart::find($value);  //get the cart item record
            $carts->orderID=$orderID->id;  //binding the orderID value with record
            $carts->save();
        }

        $email='tanchyiruay@gmail.com';
        Notification::route('mail',$email)->notify(new \App\Notifications\orderPaid($email));
        Session::flash('success','Booking successfully!');
        
        return back();
    }

    public function showOrder(){
        $orders=DB::table('my_orders')

        ->select('my_orders.id','my_orders.amount','my_orders.created_at')
        ->where('my_orders.userID','=',Auth::id())
        //->get();
        ->paginate(6);
        return view('myOrder')->with('orders',$orders);
    }

    public function showAllOrder(){
        $orders=DB::table('my_orders')

        ->select('my_orders.id','my_orders.userID','my_orders.amount','my_orders.created_at')
        ->paginate(6);

        return view('showAllOrder')->with('orders',$orders);
    }

    public function pdfReport(){
        $orders = DB::table('my_Orders')
        ->select('my_orders.id','my_orders.userID','my_orders.amount','my_orders.created_at')
        ->get();
        $pdf=PDF::loadView('salesReport',compact('orders'));

        return $pdf->download('report.pdf');
    }
}

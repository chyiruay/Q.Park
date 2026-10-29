<?php

namespace App\Http\Controllers;

use Stripe;

use Illuminate\Http\Request;
use App\Models\myOrder;
use App\Models\myCart;
use Auth;
use DB;
use Illuminate\Support\Str;
use Notification;
use PDF;

class PaymentController extends Controller
{
    //auth now lives on the routes; the old _construct() typo meant it never ran

    /**
     * Hands the customer to Stripe's hosted card page. Card details never touch
     * this app, so nothing here is in PCI scope.
     */
    public function paymentPost(Request $request)
    {
        $request->validate([
            'cid'=>'required|array',
            'cid.*'=>'integer',
        ],[
            'cid.required'=>'Tick the booking you want to pay for.',
            'cid.array'=>'Tick the booking you want to pay for.',
            'cid.*.integer'=>'That booking reference is not valid.',
        ]);

        $items=$this->pendingItems($request->input('cid'));

        //every refusal belongs back on the cart, whatever the request's referer was
        if($items->isEmpty()){
            return redirect()->route('myCart')->withErrors('Those bookings are no longer in your cart.');
        }

        if(!config('services.stripe.secret')){
            return redirect()->route('myCart')
                ->withErrors('Card payment is not configured yet. Set STRIPE_KEY and STRIPE_SECRET in .env.');
        }

        Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try{
            //price comes from the products table, never from the posted "subtotal" field
            $session=Stripe\Checkout\Session::create([
                'mode'=>'payment',
                'customer_email'=>Auth::user()->email,
                'line_items'=>$items->map(fn($item)=>[
                    'quantity'=>1,
                    'price_data'=>[
                        'currency'=>'myr',
                        'unit_amount'=>(int) round($item->price*$item->cartQty*100),
                        'product_data'=>[
                            'name'=>$item->name.' - space '.$item->space,
                            'description'=>$item->cartQty.' '.Str::plural('hour',$item->cartQty),
                        ],
                    ],
                ])->all(),
                /* which rows are being paid for is kept at Stripe, not in the browser,
                   so the return trip can't swap in someone else's bookings.
                   shortcut: metadata values cap at 500 chars, so ~80 rows per checkout.
                   Upgrade to a payments table if carts ever get that big. */
                'metadata'=>['cids'=>$items->pluck('cid')->implode(',')],
                'success_url'=>route('payment.return').'?session_id={CHECKOUT_SESSION_ID}',
                //?cancelled tells the cart to say so; Stripe's redirect carries no flash session
                'cancel_url'=>route('myCart').'?cancelled=1',
            ]);
        }catch(\Throwable $e){
            report($e);
            return redirect()->route('myCart')
                ->withErrors('Could not reach the payment provider. Please try again.');
        }

        return redirect()->away($session->url);
    }

    /**
     * Stripe redirects here afterwards. The browser only carries a session id —
     * whether money actually moved is asked of Stripe, never assumed from the URL.
     */
    public function paymentReturn(Request $request)
    {
        $request->validate(['session_id'=>'required|string']);

        Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try{
            $session=Stripe\Checkout\Session::retrieve($request->input('session_id'));
        }catch(\Throwable $e){
            report($e);
            return redirect()->route('myCart')
                ->withErrors('Could not confirm that payment. Check your order history before trying again.');
        }

        if($session->payment_status!=='paid'){
            return redirect()->route('myCart')->withErrors('That payment was not completed.');
        }

        $items=$this->pendingItems(array_filter(explode(',',(string) ($session->metadata->cids ?? ''))));

        //an earlier visit already stamped these rows; refreshing must not buy them twice
        if($items->isEmpty()){
            return redirect()->route('myOrder')
                ->with('success','That payment is already recorded. Here are your orders.');
        }

        $newOrder=myOrder::create([
            'paymentStatus'=>$session->payment_status,   //Stripe's word, not a hardcoded 'Done'
            'userID'=>Auth::id(),
            'amount'=>$session->amount_total/100,        //what Stripe actually collected
        ]);

        myCart::whereIn('id',$items->pluck('cid'))
            ->where('userID',Auth::id())
            ->where('orderID','')
            ->update(['orderID'=>$newOrder->id]);

        /* The money has moved and the order is recorded, so a mail outage must not
           take the confirmation page down with it — log it and carry on. */
        try{
            $email=Auth::user()->email;   //the customer's receipt, not the developer's inbox
            Notification::route('mail',$email)->notify(new \App\Notifications\orderPaid($email));
        }catch(\Throwable $e){
            report($e);
        }

        return redirect()->route('myOrder')->with('success','Payment received. Your booking is confirmed!');
    }

    /** Ticked rows that are still unpaid and belong to the signed-in customer. */
    private function pendingItems(array $cids)
    {
        return DB::table('my_carts')
            ->leftjoin('products','products.id','=','my_carts.productID')
            ->select('my_carts.id as cid','my_carts.quantity as cartQty','my_carts.space','products.price','products.name')
            ->whereIn('my_carts.id',$cids)
            ->where('my_carts.orderID','=','')
            ->where('my_carts.userID','=',Auth::id())
            ->get();
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

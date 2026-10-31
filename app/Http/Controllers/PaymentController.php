<?php

namespace App\Http\Controllers;

use Stripe;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\myOrder;
use App\Models\myCart;
use App\Models\User;
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

        $items=$this->pendingItems($request->input('cid'),Auth::id());

        //every refusal belongs back on the cart, whatever the request's referer was
        if($items->isEmpty()){
            return redirect()->route('myCart')->withErrors('Those bookings are no longer in your cart.');
        }

        /* A row whose hold expired may have lost its space. Check and renew one row at
           a time, so two expired rows in this cart can't both pass for the same slot. */
        $lost=[];
        foreach($items as $item){
            if(!$item->date){
                continue;   //rows from before bookings had a date have no slot to check
            }
            $start=Carbon::parse($item->date.' '.$item->startTime);
            //locked like add(), or a new booking could slip in between the check and the renewal
            $taken=myCart::whileLocked($item->productID,function() use ($item,$start){
                if(myCart::isTaken($item->productID,$item->space,$start,(int) $item->cartQty,$item->cid)){
                    return true;
                }
                myCart::where('id',$item->cid)->update(['created_at'=>now()]);
                return false;
            });
            if($taken){
                //same date/time format the cart shows, so the customer can find the row
                $lost[]=$item->name.', space '.$item->space.' on '.$start->format('d M Y, h:i A');
            }
        }
        if($lost){
            return redirect()->route('myCart')->withErrors(
                implode('; ',$lost).' was booked by someone else while it sat in your cart. Edit it to another space or time, or remove it.'
            );
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
                //uid lets the webhook, which has no signed-in user, find whose rows these are
                'metadata'=>['cids'=>$items->pluck('cid')->implode(','),'uid'=>Auth::id()],
                'success_url'=>route('payment.return').'?session_id={CHECKOUT_SESSION_ID}',
                //?cancelled tells the cart to say so; Stripe's redirect carries no flash session
                'cancel_url'=>route('myCart').'?cancelled=1',
                //Stripe's minimum is 30 minutes from its own clock; must stay under myCart::HOLD_MINUTES
                'expires_at'=>now()->addMinutes(31)->timestamp,
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

        //an earlier visit, or the webhook, already stamped these rows; refreshing must not buy them twice
        if(!$this->recordPayment($session,Auth::id())){
            return redirect()->route('myOrder')
                ->with('success','That payment is already recorded. Here are your orders.');
        }

        return redirect()->route('myOrder')->with('success','Payment received. Your booking is confirmed!');
    }

    /**
     * Stripe calls this itself once a checkout is paid, so the order is recorded
     * even when the customer closes the tab before the return redirect.
     * The signature check is what stands in for a signed-in user here.
     */
    public function webhook(Request $request)
    {
        try{
            $event=Stripe\Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret')
            );
        }catch(\Throwable $e){
            return response('Invalid signature', 400);
        }

        $session=$event->data->object;

        //async methods (e.g. FPX) complete unpaid and settle later in async_payment_succeeded
        if(in_array($event->type,['checkout.session.completed','checkout.session.async_payment_succeeded'])
            && $session->payment_status==='paid'){
            //an exception here returns 500, so Stripe retries the delivery
            $this->recordPayment($session,(int) ($session->metadata->uid ?? 0));
        }

        return response('ok');
    }

    /**
     * Turns a paid Stripe session into an order. Both the return page and the
     * webhook land here, often at the same moment, so the rows are locked and
     * only the first caller records anything. Returns false if already recorded.
     */
    private function recordPayment($session, int $userId): bool
    {
        $recorded=DB::transaction(function() use ($session,$userId){
            $items=$this->pendingItems(array_filter(explode(',',(string) ($session->metadata->cids ?? ''))),$userId,true);

            if($items->isEmpty()){
                return false;
            }

            $newOrder=myOrder::create([
                'paymentStatus'=>$session->payment_status,   //Stripe's word, not a hardcoded 'Done'
                'userID'=>$userId,
                'amount'=>$session->amount_total/100,        //what Stripe actually collected
            ]);

            myCart::whereIn('id',$items->pluck('cid'))
                ->where('userID',$userId)
                ->where('orderID','')
                ->update(['orderID'=>$newOrder->id]);

            return true;
        });

        /* The money has moved and the order is recorded, so a mail outage must not
           take the confirmation page down with it — log it and carry on. */
        if($recorded){
            try{
                $email=User::findOrFail($userId)->email;   //the customer's receipt, not the developer's inbox
                Notification::route('mail',$email)->notify(new \App\Notifications\orderPaid($email));
            }catch(\Throwable $e){
                report($e);
            }
        }

        return $recorded;
    }

    /** Ticked rows that are still unpaid and belong to the given customer. */
    private function pendingItems(array $cids, ?int $userId, bool $lock=false)
    {
        return DB::table('my_carts')
            ->leftjoin('products','products.id','=','my_carts.productID')
            ->select('my_carts.id as cid','my_carts.quantity as cartQty','my_carts.space','my_carts.productID','my_carts.date','my_carts.startTime','products.price','products.name')
            ->whereIn('my_carts.id',$cids)
            ->where('my_carts.orderID','=','')
            ->where('my_carts.userID','=',$userId)
            ->when($lock,fn($q)=>$q->lockForUpdate())
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

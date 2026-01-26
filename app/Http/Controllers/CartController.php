<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\myCart;
use Auth;
use Session;

class CartController extends Controller
{
    public function _construct(){
        $this->middleware('auth');  //the construct require use login before access the controller function 
    }

    public function add(){
        $r=request();
        $addItem=myCart::create([
            'quantity'=>$r->quantity,
            'orderID'=>'',
            'space'=>$r->space,
            'productID'=>$r->id,
            'userID'=>Auth::id(),
        ]);
        Session::flash('success',"Product add succesful!");
        return redirect()->route('myCart');
    }

    public function showMyCart(){
        $carts=DB::table('my_carts')
        ->leftjoin('products','products.id','=','my_carts.productID')
        ->select('my_carts.quantity as cartQty','my_carts.id as cid','my_carts.space as space','products.*')
        ->where('my_carts.orderID','=','')  //the item havent make payment
        ->where('my_carts.userID','=',Auth::id())
        ->get();

        //select my_carts.quantity as cartQty, my_carts.id as cid, products.* from my_carts left join products 
        //on products.id=my_carts.productID where my_cart.orderID='' and my_carts.userID='Auth::id()'
        
        return view('myCart')->with('carts',$carts);
    }

    public function delete($id){
        $delete=myCart::find($id);
        $delete->delete();  //run SQL delete from my_Cart where id=$id
        return redirect()->route('myCart');
    }
    
}

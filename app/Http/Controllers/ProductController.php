<?php

namespace App\Http\Controllers;

use DB; //import

use App\Models\Product; //import
use App\Models\Category; //import

use Session;

class ProductController extends Controller
{
    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('insertProduct')->with('categoryID', $categories);
    }

    public function store(){
        $r=request();
        $image=$r->file('product-image');
        $image->move('images',$image->getClientOriginalName());
        $imageName=$image->getClientOriginalName();

        $r=request();
        $floorPlan=$r->file('product-floorPlan');
        $floorPlan->move('floorPlan',$floorPlan->getClientOriginalName());
        $fpName=$floorPlan->getClientOriginalName();

        $addProduct=Product::create([

            'name'=>$r->productName,
            'price'=>$r->price,
            'image'=>$imageName,
            'floorPlan'=>$fpName,
            'categoryID'=>$r->categoryID,
        ]);
        Session::flash('success',"Parking added!");
        return redirect()->route('viewProduct'); //after insert redirect to view product
    }
    
    public function order($id){

        $products=Product::all()->where('id',$id);
        //select * from products where id='$id'
        return view('order')->with('products',$products)
                                    ->with('categories',Category::all());
    }
    public function orderList(){
        $r=request();
        $products=Product::find($r->hid); //retrive the record based on id
        if($r->file('product-image')!=''){
            $image=$r->file('product-image');
            $image->move('images',$image->getClientOriginalName()); //images is the local store
            $imageName=$image->getClientOriginalName(); //upload image
            $products->image=$imageName; //update product table record
        }

        $r=request();
        if($r->file('product-floorPlan')!=''){
            $floorPlan=$r->file('product-floorPlan');
            $floorPlan->move('floorPlan',$floorPlan->getClientOriginalName()); //images is the local store
            $fpName=$floorPlan->getClientOriginalName(); //upload image
            $products->floorPlan=$fpName; //update product table record
        }
        $products->name=$r->productName;
        $products->price=$r->price;
        $products->categoryID=$r->categoryID;
        $products->save();

        return redirect()->route('myCart');

    }

    public function view(){
        $product=DB::table('products') 
        ->leftjoin('categories','categories.id','=','products.categoryID')
        ->select('products.*','categories.id as catid','categories.name as catname')
        ->get();
        return view('showProduct')->with('products',$product);
    }

    public function viewAll(){
        $product=Product::all(); //apply SQL select * from products
        return view('products')->with('products',$product);
    }

    public function searchProduct(){
        $r=request();
        $keyword=$r->keyword;
        $product=DB::table('products')
        ->where('products.name','like','%'.$keyword.'%') 
        //select * from products where name like '%$keyword%'
        ->get();

        return view('products')->with('products',$product);
    }

    public function edit($id){

        $products=Product::all()->where('id',$id);
        //select * from products where id='$id'
        return view('editProduct')->with('products',$products)
                                    ->with('categories',Category::all());
    }

    public function update(){
        $r=request();
        $products=Product::find($r->hid); //retrive the record based on id
        if($r->file('product-image')!=''){
            $image=$r->file('product-image');
            $image->move('images',$image->getClientOriginalName()); //images is the local store
            $imageName=$image->getClientOriginalName(); //upload image
            $products->image=$imageName; //update product table record
        }
        if($r->file('product-floorPlan')!=''){
            $floorPlan=$r->file('product-floorPlan');
            $floorPlan->move('floorPlan',$floorPlan->getClientOriginalName()); //images is the local store
            $fpName=$floorPlan->getClientOriginalName(); //upload image
            $products->floorPlan=$fpName; //update product table record
        }
        $products->name=$r->productName;
        $products->price=$r->price;
        $products->categoryID=$r->categoryID;
        $products->save();
        Session::flash('success',"Parking updated successful!");

        return redirect()->route('viewProduct');

    }

    public function productDetail($id){
        $product=Product::all()->where('categoryID',$id); //apply SQL select * from products
        return view('productDetail')->with('products',$product);
    }

    public function delete($id){
        $delete=Product::find($id);
        $delete->delete();  //run SQL delete from my_Cart where id=$id
        return redirect()->route('viewProduct');
    }
}
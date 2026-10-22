<?php

namespace App\Http\Controllers;

use DB; //import

use App\Models\Product; //import
use App\Models\Category; //import

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

use Session;

class ProductController extends Controller
{
    /** Shared by store() and update(); 4 MB is roughly the largest image committed. */
    private const IMAGE_RULES = 'image|mimes:jpg,jpeg,png,webp|max:4096';

    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('insertProduct')->with('categoryID', $categories);
    }

    public function store(Request $request){
        $data=$request->validate([
            'productName'=>'required|string|max:255',
            'price'=>'required|numeric|min:0|max:9999',
            'categoryID'=>'required|exists:categories,id',
            'product-image'=>'required|'.self::IMAGE_RULES,
            'product-floorPlan'=>'required|'.self::IMAGE_RULES,
        ]);

        $addProduct=Product::create([

            'name'=>$data['productName'],
            'price'=>$data['price'],
            'image'=>$this->storeUpload($request->file('product-image'),'images'),
            'floorPlan'=>$this->storeUpload($request->file('product-floorPlan'),'floorPlan'),
            'categoryID'=>(string) $data['categoryID'],
        ]);
        Session::flash('success',"Parking added!");
        return redirect()->route('viewProduct'); //after insert redirect to view product
    }

    public function order($id){

        $product=Product::findOrFail($id);  //404 instead of a blank page for a bad id
        return view('order')->with('products',[$product])
                                    ->with('categories',Category::all());
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
        ->leftjoin('categories','categories.id','=','products.categoryID')
        ->select('products.*')
        ->where('products.name','like','%'.$keyword.'%')
        ->orWhere('categories.name','like','%'.$keyword.'%')
        //select products.* from products left join categories on categories.id=products.categoryID
        //where products.name like '%$keyword%' or categories.name like '%$keyword%'
        ->get();

        return view('products')->with('products',$product);
    }

    public function edit($id){

        $product=Product::findOrFail($id);
        return view('editProduct')->with('products',[$product])
                                    ->with('categories',Category::all());
    }

    public function update(Request $request){
        $data=$request->validate([
            'hid'=>'required|exists:products,id',
            'productName'=>'required|string|max:255',
            'price'=>'required|numeric|min:0|max:9999',
            'categoryID'=>'required|exists:categories,id',
            'product-image'=>'nullable|'.self::IMAGE_RULES,
            'product-floorPlan'=>'nullable|'.self::IMAGE_RULES,
        ]);

        $products=Product::findOrFail($data['hid']);
        if($request->file('product-image')){
            $products->image=$this->storeUpload($request->file('product-image'),'images');
        }
        if($request->file('product-floorPlan')){
            $products->floorPlan=$this->storeUpload($request->file('product-floorPlan'),'floorPlan');
        }
        $products->name=$data['productName'];
        $products->price=$data['price'];
        $products->categoryID=(string) $data['categoryID'];
        $products->save();
        Session::flash('success',"Parking updated successful!");

        return redirect()->route('viewProduct');

    }

    public function productDetail($id){
        $product=Product::all()->where('categoryID',$id); //apply SQL select * from products
        return view('productDetail')->with('products',$product);
    }

    public function delete($id){
        $delete=Product::findOrFail($id);
        $delete->delete();  //run SQL delete from products where id=$id
        Session::flash('success',"Parking deleted!");
        return redirect()->route('viewProduct');
    }

    /**
     * Uploads keep a server-generated name: the client filename let one upload
     * silently overwrite another (and put the client in control of the extension).
     */
    private function storeUpload(UploadedFile $file, string $dir): string
    {
        $name=$file->hashName();
        $file->move(public_path($dir),$name);

        return $name;
    }
}

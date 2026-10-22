<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB; //import

use App\Models\Product; //import
use App\Models\Category; //import

use Session; 

class CategoryController extends Controller
{
    public function insert()
    {
        $categories = \App\Models\Category::all();
        return view('insertCategory')->with('categoryID', $categories);
    }

    public function store(Request $request){
        $data=$request->validate([
            'categoryID'=>'nullable|integer|min:1|unique:categories,id',
            'categoryName'=>'required|string|max:255|unique:categories,name',
        ]);

        $addCategory=Category::create([
            'id'=>$data['categoryID'],  //'id' is  database field name, categoryID is HTML input name
            'name'=>$data['categoryName'],
        ]);
        return redirect()->route('viewCategory'); //after insert redirect to view category
    }


    public function view(){
        $category=Category::all(); //apply SQL select * from categories
        return view('showCategory')->with('categories',$category);
    }


    public function index(){
        $category=Category::all(); //apply SQL select * from categories
        return view('insertCategory')->with('categories',$category);
    }


    public function edit($id){

        $category=Category::findOrFail($id);
        return view('editCategory')->with('categories',[$category]);
    }

    public function update(Request $request){
        $data=$request->validate([
            'hid'=>'required|exists:categories,id',
            'categoryName'=>'required|string|max:255',
        ]);

        $category=Category::findOrFail($data['hid']); //retrive the record based on id
        $category->name=$data['categoryName'];
        $category->save();
        Session::flash('success',"Category updated successful!");

        return redirect()->route('viewCategory');

    }

    public function delete($id){
        $delete=Category::findOrFail($id);

        //products store categoryID as a plain string, so deleting the category
        //would leave them pointing at nothing and break /viewDetail
        if(Product::where('categoryID',(string) $delete->id)->exists()){
            return redirect()->route('viewCategory')
                ->withErrors('Move or delete this category\'s car parks first.');
        }

        $delete->delete();  //run SQL delete from categories where id=$id
        return redirect()->route('viewCategory');
    }
}

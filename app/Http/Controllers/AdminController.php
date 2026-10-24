<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(){
        return view('admin', [
            'customers' => DB::table('users')->where('isAdmin', false)->count(),
            'products' => DB::table('products')->count(),
            'categories' => DB::table('categories')->count(),
            'orders' => DB::table('my_orders')->count(),
            'revenue' => DB::table('my_orders')->sum('amount'),
            'feedback' => DB::table('feedback')->count(),
        ]);
    }
}

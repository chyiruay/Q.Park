<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('Help');

Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');

Route::get('/viewUser', [App\Http\Controllers\UserController::class, 'viewAllUser'])->name('viewUser');

Route::get('/viewProfile', [App\Http\Controllers\UserController::class, 'showUser'])->name('viewProfile');
Route::get('/editProfile/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('editProfile');
Route::post('/updateProfile', [App\Http\Controllers\UserController::class, 'update'])->name('updateProfile');
Route::get('/editPassword/{id}', [App\Http\Controllers\UserController::class, 'editPassword'])->name('editPassword');
Route::post('/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('updatePassword');
Route::get('/deleteAccount', [App\Http\Controllers\UserController::class, 'deleteAccount'])->name('deleteAccount');
Route::get('/deleteUser/{id}', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('deleteUser');

Route::get('/order/{id}', [App\Http\Controllers\ProductController::class, 'order'])->name('Order');
Route::get('/orderlist', [App\Http\Controllers\ProductController::class, 'orderList'])->name('orderList');

Route::get('/insertFeedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('insertFeedback');
Route::post('/insertFeedback/store', [App\Http\Controllers\FeedbackController::class, 'store'])->name('addFeedback');
Route::get('/viewFeedback', [App\Http\Controllers\FeedbackController::class, 'view'])->name('viewFeedback');

Route::get('/products', [App\Http\Controllers\ProductController::class, 'viewAll'])->name('Product');

Route::get('/insertCategory', [App\Http\Controllers\CategoryController::class, 'index'])->name('insertCategory');
Route::get('/editCategory/{id}', [App\Http\Controllers\CategoryController::class, 'edit'])->name('editCategory');
Route::post('/updateCategory', [App\Http\Controllers\CategoryController::class, 'update'])->name('updateCategory');
Route::post('/insertCategory/store', [App\Http\Controllers\CategoryController::class, 'store'])->name('addCategory');
Route::get('/deleteCategory/{id}', [App\Http\Controllers\CategoryController::class, 'delete'])->name('deleteCategory');

Route::get('/viewCategory', [App\Http\Controllers\CategoryController::class, 'view'])->name('viewCategory');

Route::post('/insertProduct/store', [App\Http\Controllers\ProductController::class, 'store'])->name('addProduct');
Route::get('/viewProduct', [App\Http\Controllers\ProductController::class, 'view'])->name('viewProduct');
Route::post('/product', [App\Http\Controllers\ProductController::class, 'searchProduct'])->name('search.product');

Route::get('/insertProduct', [App\Http\Controllers\ProductController::class, 'create'])->name('insertProduct');
Route::get('/editProduct/{id}', [App\Http\Controllers\ProductController::class, 'edit'])->name('editProduct');
Route::post('/updateProduct', [App\Http\Controllers\ProductController::class, 'update'])->name('updateProduct');
Route::get('/deleteProduct/{id}', [App\Http\Controllers\ProductController::class, 'delete'])->name('deleteProduct');

Route::get('/viewDetail/{id}', [App\Http\Controllers\ProductController::class, 'productDetail'])->name('viewDetail');

Route::post('/addCart', [App\Http\Controllers\CartController::class, 'add'])->name('add.to.cart');
Route::get('/myCart', [App\Http\Controllers\CartController::class, 'showMyCart'])->name('myCart');
Route::get('/deleteItem/{id}', [App\Http\Controllers\CartController::class, 'delete'])->name('delete.Item');

Route::post('\checkout', [App\Http\Controllers\PaymentController::class, 'paymentPost'])->name('payment.post');
Route::get('/myOrder', [App\Http\Controllers\PaymentController::class, 'showOrder'])->name('myOrder');
Route::get('/allOrder', [App\Http\Controllers\PaymentController::class, 'showAllOrder'])->name('allOrder');
Route::get('/salesReport', [App\Http\Controllers\PaymentController::class, 'pdfReport'])->name('salesReport');


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Grouped by who may reach them. Auth used to live in controller constructors,
| where a `_construct()` typo silently disabled it on the cart and payment
| controllers; keeping the gate here makes an unprotected route visible in
| `php artisan route:list`.
|
| Anything that writes or deletes is POST so the CSRF token is checked —
| a GET delete fires on any prefetch, crawler or third-party <img> tag.
|
*/

/* Public shopfront. 'customer' sends a signed-in admin to /admin instead;
   guests and customers pass straight through. */
Route::middleware('customer')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('Help');
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'viewAll'])->name('Product');
    Route::get('/viewDetail/{id}', [App\Http\Controllers\ProductController::class, 'productDetail'])->name('viewDetail');
    Route::post('/product', [App\Http\Controllers\ProductController::class, 'searchProduct'])->name('search.product');
});

/* Separate admin sign-in. LoginController::credentials() reads the path, so each
   form only accepts accounts of its own kind. 'guest' comes from the controller. */
Route::get('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

/* Stripe's server-to-server call: no session, so no auth group. The signature is the gate. */
Route::post('/stripe/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('stripe.webhook');

/* Any signed-in account: admins manage their own profile too. */
Route::middleware('auth')->group(function () {
    Route::get('/viewProfile', [App\Http\Controllers\UserController::class, 'showUser'])->name('viewProfile');
    Route::get('/editProfile/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('editProfile');
    Route::post('/updateProfile', [App\Http\Controllers\UserController::class, 'update'])->name('updateProfile');
    Route::get('/editPassword/{id}', [App\Http\Controllers\UserController::class, 'editPassword'])->name('editPassword');
    Route::post('/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('updatePassword');
    Route::post('/deleteAccount', [App\Http\Controllers\UserController::class, 'destroyAccount'])->name('deleteUser');
});

/* Booking and payment: customers only. */
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/order/{id}', [App\Http\Controllers\ProductController::class, 'order'])->name('Order');

    Route::get('/insertFeedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('insertFeedback');
    Route::post('/insertFeedback/store', [App\Http\Controllers\FeedbackController::class, 'store'])->name('addFeedback');
    Route::get('/myFeedback', [App\Http\Controllers\FeedbackController::class, 'history'])->name('myFeedback');

    Route::post('/addCart', [App\Http\Controllers\CartController::class, 'add'])->name('add.to.cart');
    Route::get('/myCart', [App\Http\Controllers\CartController::class, 'showMyCart'])->name('myCart');
    Route::get('/editItem/{id}', [App\Http\Controllers\CartController::class, 'edit'])->name('edit.Item');
    Route::post('/updateItem/{id}', [App\Http\Controllers\CartController::class, 'update'])->name('update.Item');
    Route::post('/deleteItem/{id}', [App\Http\Controllers\CartController::class, 'delete'])->name('delete.Item');

    Route::post('/checkout', [App\Http\Controllers\PaymentController::class, 'paymentPost'])->name('payment.post');
    /* Stripe sends the customer back here. A top-level GET navigation, so the
       SameSite=lax session cookie still rides along and Auth::id() is available. */
    Route::get('/checkout/return', [App\Http\Controllers\PaymentController::class, 'paymentReturn'])->name('payment.return');
    Route::get('/myOrder', [App\Http\Controllers\PaymentController::class, 'showOrder'])->name('myOrder');
});

/* Admins only */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
    Route::get('/viewUser', [App\Http\Controllers\UserController::class, 'viewAllUser'])->name('viewUser');

    Route::get('/insertProduct', [App\Http\Controllers\ProductController::class, 'create'])->name('insertProduct');
    Route::post('/insertProduct/store', [App\Http\Controllers\ProductController::class, 'store'])->name('addProduct');
    Route::get('/viewProduct', [App\Http\Controllers\ProductController::class, 'view'])->name('viewProduct');
    Route::get('/editProduct/{id}', [App\Http\Controllers\ProductController::class, 'edit'])->name('editProduct');
    Route::post('/updateProduct', [App\Http\Controllers\ProductController::class, 'update'])->name('updateProduct');
    Route::post('/deleteProduct/{id}', [App\Http\Controllers\ProductController::class, 'delete'])->name('deleteProduct');

    Route::get('/insertCategory', [App\Http\Controllers\CategoryController::class, 'index'])->name('insertCategory');
    Route::post('/insertCategory/store', [App\Http\Controllers\CategoryController::class, 'store'])->name('addCategory');
    Route::get('/viewCategory', [App\Http\Controllers\CategoryController::class, 'view'])->name('viewCategory');
    Route::get('/editCategory/{id}', [App\Http\Controllers\CategoryController::class, 'edit'])->name('editCategory');
    Route::post('/updateCategory', [App\Http\Controllers\CategoryController::class, 'update'])->name('updateCategory');
    Route::post('/deleteCategory/{id}', [App\Http\Controllers\CategoryController::class, 'delete'])->name('deleteCategory');

    Route::get('/viewFeedback', [App\Http\Controllers\FeedbackController::class, 'view'])->name('viewFeedback');
    Route::get('/allOrder', [App\Http\Controllers\PaymentController::class, 'showAllOrder'])->name('allOrder');
    Route::get('/salesReport', [App\Http\Controllers\PaymentController::class, 'pdfReport'])->name('salesReport');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'customer'])
    ->name('home');

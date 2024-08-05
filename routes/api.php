<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetRequestController;
use App\Http\Controllers\BackOffice\BrandController;
use App\Http\Controllers\BackOffice\CategoryController;
use App\Http\Controllers\FrontOffice\Client\ClientController;
use App\Http\Controllers\BackOffice\MessageController;
use App\Http\Controllers\BackOffice\OrderController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\BackOffice\ProductController;
use App\Http\Controllers\BackOffice\SubcategoryController;
use App\Http\Controllers\FrontOffice\Instagrammer\ProductInstagrammerController;
use App\Http\Controllers\FrontOffice\Instagrammer\InstagrammerController;
use App\Http\Controllers\FrontOffice\Provider\ProductProviderController;
use App\Http\Controllers\FrontOffice\Provider\ProviderController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  //  return $request->user();
//});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('user', 'AuthenticatedUser');
    Route::post('forgetPassword', 'forgetPassWord');
    Route::post('verifCode', 'verifCode');
    Route::put('changePassword', 'changePassword');
    Route::put('updateUserPassword/{id}', 'updateUserPassword');
   
});


//user
Route::prefix('users')->group(function () {
  Route::post('/reset-password-request', [PasswordResetRequestController::class, 'sendPasswordResetEmail']);
  Route::post('/change-password', [ChangePasswordController::class, 'passwordResetProcess']);
  Route::get('/user/{Role}',[UserController::class, 'getUsersByRole']);
  Route::get('/',[UserController::class, 'index']);
  Route::post('/save',[UserController::class, 'store']); // méme methode  que register dans AuthController
  Route::get('/show/{id}',[UserController::class, 'show']);
  Route::delete('/destroy/{id}',[UserController::class, 'destroy']);
  Route::post('/update/{id}',[UserController::class, 'update']);
  Route::get('/filter', [UserController::class, 'filterUser']);
  Route::post('/updateUserStatus/{id}', [UserController::class, 'updateUserStatus']);
});

//Category
Route::prefix('categories')->group(function(){

  Route::get('/',[CategoryController::class,'index']);
  Route::post('/create-category',[CategoryController::class,'store']);
  Route::get('/show/{id}',[CategoryController::class,'show']);
  Route::put('/update/{id}',[CategoryController::class,'update']);
  Route::delete('/delete/{id}',[CategoryController::class,'destroy']);
});

//subCategory
Route::prefix('subCategories')->group(function () {
  Route::get('/', [SubcategoryController::class, 'index']);
  Route::post('/save', [SubcategoryController::class, 'store']);
  Route::put('/update/{id}', [SubcategoryController::class, 'update']);
  Route::delete('/delete/{id}', [SubcategoryController::class, 'destroy']);
  Route::get('/show/{id}',[SubcategoryController::class, 'show']);
  //Route::get('filterSubcategory', [SubcategoryController::class, 'filterSubcategory']);

});

//brands

Route::prefix('brands')->group(function(){
  Route::get('/', [BrandController::class, 'index']);
  Route::post('/save', [BrandController::class, 'store']);
  Route::put('/update/{id}',[BrandController::class,'update']);
  Route::delete('/delete/{id}',[BrandController::class,'destroy']);
  Route::get('show/{id}',[BrandController::class,'show']);
});


//product
Route::prefix('products')->group(function () {
  Route::get('/', [ProductController::class, 'index']);
  Route::post('/save', [ProductController::class, 'store']);
  Route::post('/update/{id}', [ProductController::class, 'update']);
  Route::post('/addImages/{id}', [ProductController::class, 'addImages']);
  Route::post('/deleteImages/{id}', [ProductController::class, 'deleteImages']);
  Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
  Route::get('filterProduct', [ProductController::class, 'filterProduct']);
  Route::get('/show/{id}',[ProductController::class, 'show']);
  Route::post('/updateProductStatus/{id}', [ProductController::class, 'updateProductStatus']);
  Route::get('/colors',[ProductController::class, 'colors']);
  Route::get('/sizes',[ProductController::class, 'sizes']);


});



//messages
Route::prefix('message')->group(function () {
  Route::get('/', [MessageController::class, 'index']);  
  Route::post('/sendAdminMessage', [MessageController::class, 'sendAdminMessage']);
  Route::post('/update/{id}', [MessageController::class, 'update']);
  Route::delete('/delete/{id}', [MessageController::class, 'destroy']);
  Route::get('/show/{id}',[MessageController::class, 'show']);
  Route::get('/getContacts',[MessageController::class, 'getContacts']);
  Route::get('/getMessagesByProvider', [MessageController::class, 'getMessagesByProvider']);
});

//orders
Route::prefix('orders')->group(function () {
  Route::get('/', [OrderController::class, 'index']);
  Route::post('/save', [OrderController::class, 'store']);
  Route::post('/update/{id}', [OrderController::class, 'update']);
  Route::delete('/delete/{id}', [OrderController::class, 'destroy']);
  Route::get('/show/{id}',[OrderController::class, 'show']);
  Route::post('/updateOrderStatus/{id}', [OrderController::class, 'updateOrderStatus']);
  Route::get('/filterOrders', [OrderController::class, 'filterOrders']);
});

//instagrammer

Route::prefix('instagrammers')->group(function(){
  Route::get('products', [ProductInstagrammerController::class, 'index']);
  Route::post('/saveProduct', [ProductInstagrammerController::class, 'store']);
  Route::post('/updateProduct/{id}', [ProductInstagrammerController::class, 'update']);
  Route::delete('/deleteProduct/{id}', [ProductInstagrammerController::class, 'destroy']);
  Route::get('/showProduct/{id}',[ProductInstagrammerController::class, 'show']);
  Route::post('/addEchantillon', [InstagrammerController::class, 'addEchantillon']);
  Route::post('/addProductProvider', [InstagrammerController::class, 'addProductProvider']);
  Route::get('/getInstagrammerProducts', [InstagrammerController::class, 'getInstagrammerProducts']);
  Route::post('/sendProviderMessage', [InstagrammerController::class, 'sendProviderMessage']);
  Route::post('updateSelfData',[InstagrammerController::class, 'updateSelfData']);
  Route::get('/getProviderProducts', [InstagrammerController::class, 'getProviderProducts']);
  Route::get('/colors',[ProductInstagrammerController::class, 'colors']);
  Route::get('/sizes',[ProductInstagrammerController::class, 'sizes']);
  Route::get('/filterProducts', [InstagrammerController::class, 'filterProducts']);
  Route::get('/getStoreProducts', [InstagrammerController::class, 'getStoreProducts']);


});

//providers

Route::prefix('providers')->group(function(){
  Route::get('products', [ProductProviderController::class, 'index']);
  Route::get('/showProduct/{id}',[ProductProviderController::class, 'show']);
  Route::post('/saveProduct', [ProductProviderController::class, 'store']);
  Route::post('/updateProduct/{id}', [ProductProviderController::class, 'update']);
  Route::post('/updateEchantillon/{id}', [ProviderController::class, 'updateEchantillon']);
  Route::get('/getProviderProducts', [ProviderController::class, 'getProviderProducts']);
  Route::post('updateSelfData',[ProviderController::class, 'updateSelfData']);
  Route::post('/sendMessage', [ProviderController::class, 'sendProviderMessage']);
  Route::get('/colors',[ProductProviderController::class, 'colors']);
  Route::get('/sizes',[ProductProviderController::class, 'sizes']);
  Route::get('/getOrdersByProvider',[ProviderController::class, 'getOrdersByProvider']);
  Route::get('/show/{id}',[ProviderController::class, 'show']);
  Route::get('/getUserData',[ProviderController::class, 'getUserData']);
  Route::get('/getMessagesByAdmin',[ProviderController::class, 'getMessagesByAdmin']);
  Route::get('/echantillons',[ProviderController::class, 'getListEchantillons']);
  Route::get('/showEchantillon/{id}',[ProviderController::class, 'showEchantillon']);
  Route::get('/getOrderByStatus',[ProviderController::class, 'getOrderByStatus']);


});

//client
Route::prefix('clients')->group(function(){
  Route::get('/getProductById/{id}', [ClientController::class, 'getProductById']);
  Route::get('/getOrderById/{id}', [ClientController::class, 'getOrderById']);
  Route::post('/addOrder', [ClientController::class, 'addOrder']);
  Route::post('/updateOrder/{id}', [ClientController::class, 'updateOrder']);
  Route::post('/cancelOrder/{id}', [ClientController::class, 'cancelOrder']);
  Route::post('/confirmOrder/{id}', [ClientController::class, 'confirmOrder']);

});

Route::get('generate-invoice/{orderId}', [InvoiceController::class, 'generateInvoice']);
<?php


use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIsAdmin;
use App\Http\Middleware\ExtractTokenFromCookie;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;


Route::post('register',[UserController::class, 'register']);

Route::post('login',[UserController::class, 'login']);

Route::middleware('auth:api')
    ->group(function () {
        // Your routes here
        Route::post('userdetail',[UserController::class, 'userDetail']);
        Route::post('logout',[UserController::class, 'logout']);

    });



    // Route::middleware([ExtractTokenFromCookie::class,CheckIsAdmin::class, 'auth:api'])
    // ->group(function () {
    //     // Your routes here
    //     Route::post('register',[AdminController::class, 'register']);
    // });














// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\postsController;
use App\Http\Controllers\API\FollowController;
use App\Http\Controllers\API\storyController;

use App\Http\Controllers\API\AdminController;


Route::post('register',[UserController::class, 'register']);

Route::post('login',[UserController::class, 'login']);



Route::middleware('auth:api')
    ->group(function () {
        // Your routes here
        Route::get('userdetail',[UserController::class, 'userDetail']);
        Route::get('search',[UserController::class, 'search']);
        Route::post('logout',[UserController::class, 'logout']);
        Route::post('createpost',[postsController::class, 'createPost']);
        Route::get('getuserposts',[postsController::class, 'getUserPosts']);


        //storyController routes
        Route::post('createstory',[storyController::class, 'createStory']);
        Route::get('viewstory',[storyController::class, 'viewStory']);




        // FollowRequestController routes
        Route::post('sendrequest', [FollowController::class, 'sendRequest']);
        Route::post('unfollow', [FollowController::class, 'unfollow']);
        // Route::post('acceptrequest', [FollowController::class, 'acceptRequest']);
        // Route::post('cancelrequest', [FollowController::class, 'cancelRequest']);
        Route::get('getfollowing', [FollowController::class, 'getFollowing']);
        Route::get('getfollowers', [FollowController::class, 'getFollowers']);


    });



    // Route::middleware([ExtractTokenFromCookie::class,CheckIsAdmin::class, 'auth:api'])
    // ->group(function () {
    //     // Your routes here
    //     Route::post('register',[AdminController::class, 'register']);
    // });














// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\postsController;
use App\Http\Controllers\API\FollowController;
use App\Http\Controllers\API\storyController;
use App\Http\Controllers\API\analyticsController;
use App\Http\Controllers\API\AdminController;



Route::post('register',[UserController::class, 'register']);

Route::post('login',[UserController::class, 'login']);



Route::middleware('auth:api')
    ->group(function () {
        // Your routes here
        Route::get('userdetail',[UserController::class, 'userDetail']);
        Route::Post('updatedetails',[UserController::class, 'updateDetails']);
        Route::get('search',[UserController::class, 'search']);
        Route::post('logout',[UserController::class, 'logout']);
        Route::post('createreport',[UserController::class, 'createReport']);

     
        //post routes
        Route::post('createpost',[postsController::class, 'createPost']);
        Route::get('getuserposts',[postsController::class, 'getUserPosts']);
        Route::post('updatepost',[postsController::class, 'updatePost']);
        Route::post('deletepost',[postsController::class, 'deletePost']);
        Route::get('viewfollowingpost',[postsController::class, 'viewFollowingPost']);
        Route::post('likepost',[postsController::class, 'likePost']);
        Route::get('countlikes',[postsController::class, 'countLikes']);
        Route::post('createcomment',[postsController::class, 'createComment']);
        Route::get('countcomments',[postsController::class, 'countComments']);
        Route::get('viewcomments',[postsController::class, 'viewcomments']);
        Route::get('getforyouvideos', [postsController::class, 'getForYouVideos']);
        Route::post('share', [postsController::class, 'share']);
        Route::get('getpost', [postsController::class, 'getpost']);
        Route::post('createimpression', [postsController::class, 'createImpression']);
        Route::post('createview', [postsController::class, 'createView']);


        //storyController routes
        Route::post('createstory',[storyController::class, 'createStory']);
        Route::get('viewstory',[storyController::class, 'viewStory']);




        // FollowRequestController routes
        Route::post('sendrequest', [FollowController::class, 'sendRequest']);
        Route::post('unfollow', [FollowController::class, 'unfollow']);
        Route::get('getfollowing', [FollowController::class, 'getFollowing']);
        Route::get('getfollowers', [FollowController::class, 'getFollowers']);
        

        //analytics contrller routes
        Route::get('getAllLikes', [analyticsController::class, 'getAllLikes']);
        Route::get('getallcomments', [analyticsController::class, 'getAllComments']);
        Route::get('getallshares', [analyticsController::class, 'getAllShares']);
        Route::get('getallimpressions', [analyticsController::class, 'getAllImpressions']);
        Route::get('getallviews', [analyticsController::class, 'getAllViews']);
    });


    // Route::prefix('admin')->middleware(['auth:api', 'CheckIsAdmin'])->group(function () {
    Route::prefix('admin')->group(function () {
        // Admin-specific routes here
        Route::get('getreports',[AdminController::class, 'getReports']);
        Route::post('deletereport',[AdminController::class, 'deleteReport']);
        Route::get('getblockedusers',[AdminController::class, 'getBlockedUsers']);
        Route::post('blockuser',[AdminController::class, 'blockUser']);
        Route::post('blockpost',[AdminController::class, 'blockPost']);

        Route::post('unblockuser',[AdminController::class, 'unblockUser']);
        Route::get('getactiveusers',[AdminController::class, 'getActiveUsers']);
        Route::get('getinactiveusers',[AdminController::class, 'getInactiveUsers']);
        
        
        // Add more admin routes as needed
    });

    // Route::middleware([ExtractTokenFromCookie::class,CheckIsAdmin::class, 'auth:api'])
    // ->group(function () {
    //     // Your routes here
    //     Route::post('register',[AdminController::class, 'register']);
    // });














// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

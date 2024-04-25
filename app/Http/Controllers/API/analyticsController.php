<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\post;
use Illuminate\Support\Facades\Auth;


class analyticsController extends Controller
{
    //
    public function getAllLikes()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
    
        // Get all non-blocked posts by the user
        $posts = Post::where('userId', $userId)->where('isblocked', false)->get();
    
        // Sum the likes for each non-blocked post
        $totalLikes = $posts->sum('likes');
    
        // Return the total likes in a JSON response
        return response()->json(['totalLikes' => $totalLikes]);
    }



    public function getAllComments()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
    
        // Get all non-blocked posts by the user
        $posts = Post::where('userId', $userId)->where('isblocked', false)->get();
    
        // Initialize the total comments count
        $totalComments = 0;
    
        // Loop through each post and sum the comments
        foreach ($posts as $post) {
            $totalComments += $post->comments;
        }
    
        // Return the total comments in a JSON response
        return response()->json(['totalComments' => $totalComments]);
    }

    public function getAllShares()
    {
        $userId = Auth::id();
    
        $posts = Post::where('userId', $userId)
            ->where('isblocked', false)
            ->get();
    
        $totalShares = $posts->sum('shares');
    
        return response()->json(['totalShares' => $totalShares]);
    }
    
    public function getAllImpressions()
    {
        $userId = Auth::id();
    
        $posts = Post::where('userId', $userId)
            ->where('isblocked', false)
            ->get();
    
        $totalImpressions = $posts->sum('impressions');
    
        return response()->json(['totalImpressions' => $totalImpressions]);
    }


    public function getAllViews()
    {
        $userId = Auth::id();
    
        $posts = Post::where('userId', $userId)
            ->where('isblocked', false)
            ->get();
    
        $totalViews = $posts->sum('views');
    
        return response()->json(['totalViews' => $totalViews]);
    }

}

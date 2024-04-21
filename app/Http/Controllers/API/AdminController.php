<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use App\Models\post;
use App\Models\User;
class AdminController extends Controller
{
    //
    function getReports(Request $request)
    {
        // Get all reports
        $reports = DB::table('reports')
            ->select('id', 'reported_for', 'reported_by', 'post_id', 'reason')
            ->get();
    
        // Get user information for reported_by and reported_for
        $reportedByUsers = DB::table('users')
            ->whereIn('id', $reports->pluck('reported_by'))
            ->select('id', 'username', 'profilePicture')
            ->get();
    
        $reportedForUsers = DB::table('users')
            ->whereIn('id', $reports->pluck('reported_for'))
            ->select('id', 'username', 'profilePicture')
            ->get();
    
        // Get post information
        $posts = DB::table('posts')
            ->whereIn('id', $reports->pluck('post_id'))
            ->get();
    
        // Combine the data
        $combinedReports = $reports->map(function ($report) use ($reportedByUsers, $reportedForUsers, $posts) {
            $post = $posts->where('id', $report->post_id)->first();
            $reportedForUser = $reportedForUsers->where('id', $report->reported_for)->first();
    
            // Add the full URL for the post media file
            $postMediaURL = $post->media ? asset("storage/{$reportedForUser->id}/posts/" . $post->media) : null;
        
          
            return [
                'reportId' => $report->id,
                'reportedById' => $report->reported_by,
                'reportedByUsername' => $reportedByUsers->where('id', $report->reported_by)->first()->username,
                'reportedByProfilePic' => asset('storage/profile/' . $reportedByUsers->where('id', $report->reported_by)->first()->profilePicture),
                'reportedForId' => $report->reported_for,
                'reportedForUsername' => $reportedForUsers->where('id', $report->reported_for)->first()->username,
                'reportedForProfilePic' => asset('storage/profile/' . $reportedForUsers->where('id', $report->reported_for)->first()->profilePicture),
                'post' => $posts->where('id', $report->post_id)->first(),
                'mediaUrl' => $postMediaURL,
                'reason' => $report->reason,
            ];
        });
    
        // Return the combined reports in JSON format
        return response()->json(['reports' => $combinedReports]);
    }









    
function deleteReport(Request $request)
{
    // Retrieve the report_id from the request
    $reportId = $request->report_id;

    // Find and delete the report from the database
    $deleted = DB::table('reports')->where('id', $reportId)->delete();

    // Check if a report was deleted
    if ($deleted) {
        // Return a success response
        return response()->json(['message' => 'Report deleted successfully'], 200);
    } else {
        // Return an error response if no report was found with the given id
        return response()->json(['error' => 'Report not found or already deleted'], 404);
    }
}





function getBlockedUsers(Request $request)
{
    // Retrieve all users where isblocked is true
    $blockedUsers = DB::table('users')
        ->where('isblocked', true)
        ->get();

    // Add the profile picture URL to each user
    $blockedUsers->transform(function ($user) {
        $user->profilePictureUrl = asset('storage/profile/' . $user->profilePicture);
        return $user;
    });

    // Return the blocked users with profile picture URLs in JSON format
    return response()->json(['blockedUsers' => $blockedUsers]);
}





// Function to block a user
public function blockUser(Request $request)
{
    // Retrieve the user_id from the request
    $userId = $request->user_id;

    // Find the user by id
    $user = User::find($userId);
    if (!$user) {
        // Return an error response if no user was found
        return response()->json(['error' => 'User not found'], 404);
    }

    // Check if the user is an admin and prevent blocking
    if ($user->isAdmin) {
        return response()->json(['error' => 'Admin users cannot be blocked'], 403);
    }

    // Set isblocked to true and save the user
    $user->isblocked = true;
    $user->save();

    // Revoke all tokens for the user
    $user->tokens->each(function ($token, $key) {
        $token->revoke();
    });

    // Return a success response
    return response()->json(['message' => 'User blocked successfully'], 200);
}




public function blockPost(Request $request)
{
    // Retrieve the post_id from the request
    $postId = $request->post_id;

    // Find the post by id
    $post = Post::find($postId);
    if (!$post) {
        // Return an error response if no post was found
        return response()->json(['error' => 'Post not found'], 404);
    }

    // Check if the post is already blocked
    if ($post->isblocked) {
        return response()->json(['error' => 'Post is already blocked'], 403);
    }

    // Set isblocked to true and save the post
    $post->isblocked = true;
    $post->save();

    // Return a success response
    return response()->json(['message' => 'Post blocked successfully'], 200);
}


// Function to unblock a user
public function unblockUser(Request $request)
{
    // Retrieve the user_id from the request
    $userId = $request->user_id;

    // Find the user by id and set isblocked to false
    $user = User::find($userId);
    if ($user) {
        $user->isblocked = false;
        $user->save();

        // Return a success response
        return response()->json(['message' => 'User unblocked successfully'], 200);
    } else {
        // Return an error response if no user was found
        return response()->json(['error' => 'User not found'], 404);
    }
}




// Function to get active users
function getActiveUsers(Request $request)
{
    // Retrieve all active users
    $activeUsers = DB::table('users')
        ->where('isactive', true)
        ->get();

    // Add the profile picture URL to each user
    $activeUsers->transform(function ($user) {
        $user->profilePictureUrl = asset('storage/profile/' . $user->profilePicture);
        return $user;
    });

    // Return the active users with profile picture URLs in JSON format
    return response()->json(['activeUsers' => $activeUsers]);
}

// Function to get inactive users
function getInactiveUsers(Request $request)
{
    // Retrieve all inactive users
    $inactiveUsers = DB::table('users')
        ->where('isactive', false)
        ->get();

    // Add the profile picture URL to each user
    $inactiveUsers->transform(function ($user) {
        $user->profilePictureUrl = asset('storage/profile/' . $user->profilePicture);
        return $user;
    });

    // Return the inactive users with profile picture URLs in JSON format
    return response()->json(['inactiveUsers' => $inactiveUsers]);
}






}





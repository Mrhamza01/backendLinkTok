<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\post;
use App\Models\User;
use App\Models\Like;
use App\Models\comment;


use App\Models\Userpost;

use Illuminate\Support\Str;

class postsController extends Controller
{
public function createPost(Request $request)
{
    // Retrieve the authenticated user based on the token
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Validate the request
    $validator = Validator::make($request->all(), [
        'caption' => 'required|string',
        'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480', // 20MB Max
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Extract hashtags from caption
    $caption = $request->caption;
    $tags = [];
    preg_match_all('/#(\w+)/', $caption, $tags);

    // Remove hashtags from caption
    $caption = preg_replace('/#(\w+)/', '', $caption);

    // Begin a transaction
    DB::beginTransaction();

    try {
        $post = new post();
        $post->userId = $user->id;
        $post->caption = $caption;
        $post->tags = implode(',', $tags[1]); // Save tags as comma-separated values
        $post->is_scheduled = false; // Assuming posts are not scheduled by default

        // Check if media is provided
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = time() . '_' . strtolower($media->getClientOriginalExtension());
            $folder = "/{$user->id}/posts";

            // Store the file in the local storage
            Storage::disk('public')->putFileAs($folder, $media, $mediaName);

           // Get the file extension
           $extension = strtolower($media->getClientOriginalExtension());

    // Set the post type based on the media type
// Set the post type based on the media type
$post->postType = ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') ? 'photo' : 'video';            $post->media = $mediaName;
            
        }

        // Save the post
        $post->save();

        // Commit the transaction
        DB::commit();

        return response()->json(['message' => 'Post created successfully!'], 201);
    } catch (\Exception $e) {
        // Rollback the transaction
        DB::rollBack();

        // Delete the media if it was stored
        if (isset($mediaName)) {
            Storage::disk('local')->delete("{$folder}/{$mediaName}");
        }

        return response()->json(['message' => 'Failed to create post', 'error' => $e->getMessage()], 500);
    }
}





public function getUserPosts(Request $request)
{
    // Retrieve the authenticated user based on the token
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Retrieve all posts from the authenticated user
    $posts = Post::where('userId', $user->id)->get();

    // Check if the user has posts
    if ($posts->isEmpty()) {
        return response()->json(['message' => 'No posts found for this user'], 404);
    }

    // Add the full URL for each media file
    $posts->transform(function ($post) use ($user) {
        if ($post->media) {
            $post->mediaUrl = asset("storage/{$user->id}/posts/" . $post->media);
        }
        return $post;
    });
    
        

    // Return the posts with media URLs
    return response()->json(['message' => 'Posts retrieved successfully!', 'posts' => $posts], 200);
}

public function viewFollowingPost()
{
    // Get the authenticated user
    $user = Auth::user();

    // Retrieve the target IDs of users the current user follows
    $targetIds = DB::table('follows')
        ->where('user_id', $user->id)
        ->pluck('target_id');

    // Retrieve user IDs and story IDs from the userStory table
    $userPostData = DB::table('userstorys')
        ->whereIn('user_id', $targetIds)
        ->select('user_id', 'post_id')
        ->get();

    // Prepare an array to store the final result
    $result = [];

    foreach ($userPostData as $entry) {
        $user = User::find($entry->user_id); // Assuming you have a User model
        $post = post::find($entry->post_id);

       
          


        if ($user && $post) {
              // Create the media URL
              $mediaUrl = asset("storage/{$user->id}/posts/" . $post->media);
            $result[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'post_id' => $post->id,
                'mediaURL' => $mediaUrl,
                 // Assuming 'media' is the file name
            ];
        }
    }

    // You can customize the response format as needed (e.g., JSON)
    return response()->json(['followingPost' => $result]);
}


function likePost(Request $request) {
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authenticated
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Get the post_id from the request
    $postId = $request->post_id;

    // Check if the post exists
    $postExists = Post::where('id', $postId)->exists();

    if (!$postExists) {
        return response()->json(['error' => 'Post not found'], 404);
    }

    // Check if the user has already liked the post
    $existingLike = Like::where('user_id', $user->id)->where('post_id', $postId)->first();

    if ($existingLike) {
        // User has already liked the post
        return response()->json(['error' => 'You have already liked this post.'], 400);
    } else {
        // User has not liked the post yet, so create a new like
        Like::create([
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);

        return response()->json(['message' => 'Post liked successfully.'], 200);
    }
}



function countLikes(Request $request) {
    // Get the post_id from the request
    $postId = $request->post_id;

    // Count the number of likes for the post
    $likeCount = Like::where('post_id', $postId)->count();

    return response()->json(['like_count' => $likeCount], 200);
}




function createComment(Request $request) {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
        'commentText' => 'required|string|max:250',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json(['error' => 'Validation error', 'message' => $validator->errors()], 422);
    }

    try {
        // Create the comment
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $request->post_id,
            'comment' => $request->commentText,
        ]);

        return response()->json(['message' => 'Comment created successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
    }
}

function countComments(Request $request) {
    try {
        // Get the post_id from the request
        $postId = $request->post_id;

        // Count the number of comments for the post
        $commentCount = Comment::where('post_id', $postId)->count();

        return response()->json(['comment_count' => $commentCount], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
    }
}





}
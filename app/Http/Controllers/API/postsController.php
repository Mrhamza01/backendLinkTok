<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\post;
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




}
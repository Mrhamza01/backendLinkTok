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
use App\Models\Share;
use App\Models\comment;
use App\Models\Impression;

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
        'media' => 'required|file|max:20480', // 20MB Max
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
        $post = new Post(); // Correct capitalization
        $post->userId = $user->id;
        $post->caption = $caption;
        $post->tags = implode(',', $tags[1]); // Save tags as comma-separated values
        $post->is_scheduled = false; // Assuming posts are not scheduled by default

        // Check if media is provided
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = time() . '.' . $media->getClientOriginalExtension();
            $folder = "/{$user->id}/posts";

            // Store the file in the local storage
            Storage::disk('public')->putFileAs($folder, $media, $mediaName);

            // Determine the media type based on MIME type
            $mediaType = $media->getMimeType();
            $post->postType = strpos($mediaType, 'image') !== false ? 'photo' : 'video';
            $post->media = $mediaName;
        }

        // Save the post
        $post->save();

        // Now, store the user_id and post_id in the userposts table
        $userPost = new UserPost(); // Assuming you have a UserPost model
        $userPost->user_id = $user->id;
        $userPost->post_id = $post->id; // post_id is automatically set when the post is saved
        $userPost->save();

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






    public  function getpost(Request $request)
    {
        // Retrieve the authenticated user based on the token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Retrieve the post ID from the request
        $postId = $request->post_id;

        // Retrieve the post based on the post ID
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        // Check if the post is blocked
        if ($post->isblocked) {
            return response()->json(['error' => 'Post is blocked'], 403);
        }

        // Add the full URL for the media file
        if ($post->media) {
            $post->mediaUrl = asset("storage/{$user->id}/posts/" . $post->media);
        }

        // Return the post data with the media URL
        return response()->json([
            'message' => 'Post retrieved successfully!',
            'post' => $post
        ], 200);
    }

    public function getUserPosts(Request $request)
    {
        // Retrieve the authenticated user based on the token
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        // Retrieve all posts from the authenticated user that are not blocked
        $posts = Post::where('userId', $user->id)->where('isblocked', false)->get();
    
        // Check if the user has posts
        if ($posts->isEmpty()) {
            return response()->json(['message' => 'No posts found for this user'], 404);
        }
    
        // Add the full URL for each media file
        foreach ($posts as $post) {
            if ($post->media) {
                $post->mediaUrl = asset("storage/{$user->id}/posts/" . $post->media);
            }
        }
    
        // Return the updated posts with media URLs
        return response()->json([
            'message' => 'Posts retrieved and updated successfully!',
            'posts' => $posts
        ], 200);
    }
    
    

    public function viewFollowingPost()
    {
        // Get the authenticated user
        $currentUser = Auth::user();
    
        // Retrieve the target IDs of users the current user follows
        $targetIds = DB::table('follows')
            ->where('user_id', $currentUser->id)
            ->pluck('target_id');
    
        // Retrieve user IDs and post IDs from the userposts table
        $userPostData = DB::table('userposts')
            ->whereIn('user_id', $targetIds)
            ->select('user_id', 'post_id')
            ->get();
    
        $result = [];
    
        foreach ($userPostData as $entry) {
            $user = User::find($entry->user_id); // Assuming you have a User model
            $post = Post::find($entry->post_id); // Assuming you have a Post model
    
            // Check if the user and post exist and the post is not blocked
            if ($user && $post && !$post->isblocked) {
                // Create the media URL
                $mediaUrl = asset("storage/{$user->id}/posts/" . $post->media);
    
                // Retrieve username and profile picture of the user
                $username = $user->username;
                $profilePicture = $user->profile_picture; // Assuming 'profile_picture' is the field name
                $profilePictureUrl = asset('storage/profile/' . $user->profilePicture);
    
                $result[] = [
                    'user_id' => $user->id,
                    'username' => $username,
                    'profile_picture' => $profilePicture,
                    'post_id' => $post->id,
                    'likes' => $post->likes,
                    'comments' => $post->comments,
                    'mediaURL' => $mediaUrl,
                    'profilePictureUrl' => $profilePictureUrl,
                    'like_count' => $post->likes,
                    'comment_count' => $post->comments,
                ];
            }
        }
    
        // You can customize the response format as needed (e.g., JSON)
        return response()->json(['followingPost' => $result]);
    }
    
    



    function likePost(Request $request)
{
    // Get the authenticated user
    $user = Auth::user();

    // Check if the user is authenticated
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Get the post_id from the request
    $postId = $request->post_id;

    // Check if the post exists
    $post = Post::find($postId);

    if (!$post) {
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

        // Increment the likes count in the posts table
        $post->increment('likes');

        return response()->json(['message' => 'Post liked successfully.'], 200);
    }
}





    




    function createComment(Request $request){
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'commentText' => 'required|string|max:250',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'message' => $validator->errors()], 422);
        }
        $postId = $request->post_id;

        // Check if the post exists
    $post = Post::find($postId);

    if (!$post) {
        return response()->json(['error' => 'Post not found'], 404);
    }
        try {
            // Create the comment
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'comment' => $request->commentText,
            ]);
            $post->increment('comments');


            return response()->json(['message' => 'Comment created successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

   

    function viewcomments(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'message' => $validator->errors()], 422);
        }
    
        // Retrieve all comments for the post
        $comments = Comment::where('post_id', $request->post_id)->get();
    
        // Prepare an array to hold the comment data
        $commentsData = [];
    
        foreach ($comments as $comment) {
            // Find the user who made the comment
            $user = User::find($comment->user_id);
    
            // Generate the URL for the user's profile picture
            $profilePictureUrl = asset('storage/profile/' . $user->profilePicture);
    
            // Add the comment details to the array
            $commentsData[] = [
                'id'=>$comment->id,
                'username' => $user->username,
                'profilePictureUrl' => $profilePictureUrl,
                'commentText' => $comment->comment,
            ];
        }
    
        // Return the comments data as JSON
        return response()->json(['commentsData' =>$commentsData]);
    }
    


    public function share(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => 'Validation error', 'message' => $validator->errors()], 422);
    }

    $postId = $request->post_id;
    $user = auth()->user();

    // Perform operations within a database transaction
    DB::transaction(function () use ($postId, $user) {
        // Check if the post exists
        $post = Post::find($postId);
        if (!$post) {
            throw new \Exception('Post not found');
        }

        // Save in the shares table
        $share = new Share([
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);
        $share->save();

        // Increment the shares column for the post
        $post->increment('shares');

    });

    return response()->json(['success' => 'Post shared successfully']);
}





public function createImpression(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => 'Validation error', 'message' => $validator->errors()], 422);
    }

    $postId = $request->post_id;
    $user = auth()->user();

    // Perform operations within a database transaction
    DB::transaction(function () use ($postId, $user) {
        // Check if the post exists
        $post = Post::find($postId);
        if (!$post) {
            throw new \Exception('Post not found');
        }

        // Save in the shares table
        $impression = new Impression([
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);
        $impression->save();

        // Increment the shares column for the post
        $post->increment('impressions');

    });

    return response()->json(['message' => 'impressions created successfully']);
}


public function getForYouVideos()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Retrieve all video posts
    $videoPosts = Post::where('postType', 'video')->get();

    // Check if video posts are found
    if ($videoPosts->isEmpty()) {
        return response()->json(['message' => 'No videos for view'], 404);
    }
  // Shuffle the collection to randomize the order
  $shuffledPosts = $videoPosts->shuffle();

  // Create the media URL for each video post
  foreach ($shuffledPosts as $videoPost) {
      $videoPost->mediaUrl = asset("storage/{$videoPost->userId}/posts/" . $videoPost->media);
  }

  // Return all video post data along with the media URLs in random order
  return response()->json([
      'posts' => $shuffledPosts,
  ]);
}




}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Story;
use App\Models\Userstory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Jobs\ExpireStories;
use Carbon\Carbon;

class storyController extends Controller
{
    //
   

public function createStory(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

     // Validate the request
     $validator = Validator::make($request->all(), [
        'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:10000', // 10MB Max
    ]);
    
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }
    

    DB::beginTransaction();

    try {
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = time() . '_' . $media->getClientOriginalName();
            $folder = "/{$user->id}/stories";

            Storage::disk('public')->putFileAs($folder, $media, $mediaName);

            $story = new story();
            $mediaType = $media->getMimeType();
            $story->storyType = strpos($mediaType, 'image') !== false ? 'photo' : 'video';
            $story->media = $mediaName;

           
            $story->user_Id = $user->id;
            $story->media = $mediaName;
           
            $story->save();

// Save the user-story relationship in the userStory table
            $userStory = new userstory();
            $userStory->user_id = $user->id;
            $userStory->story_id = $story->id;
            $userStory->save();

            DB::commit();


          ExpireStories::dispatch()->delay(Carbon::now()->addMinute());

            return response()->json(['message' => 'Story posted successfully'], 200);
            

        }
    } catch (\Exception $e) {
        DB::rollBack();

        if (isset($mediaName)) {
            Storage::disk('public')->delete("{$folder}/{$mediaName}");
        }

        return response()->json(['error' => 'An error occurred while posting the story'], 500);
    }
}


public function viewStory()
{
    // Get the authenticated user
    $user = Auth::user();

    // Retrieve the target IDs of users the current user follows
    $targetIds = DB::table('follows')
        ->where('user_id', $user->id)
        ->pluck('target_id');

    // Retrieve user IDs and story IDs from the userStory table
    $userStoryData = DB::table('userstorys')
        ->whereIn('user_id', $targetIds)
        ->select('user_id', 'story_id')
        ->get();

    // Prepare an array to store the final result
    $result = [];

    foreach ($userStoryData as $entry) {
        $user = User::find($entry->user_id); // Assuming you have a User model
        $story = story::find($entry->story_id);

       
          


        if ($user && $story) {
              // Create the media URL
              $mediaUrl = asset("storage/{$user->id}/stories/" . $story->media);
            $result[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'story_id' => $story->id,
                'storyType'=>$story->storyType,
                'mediaURL' => $mediaUrl,
                 
            ];
        }
    }

    // You can customize the response format as needed (e.g., JSON)
    return response()->json(['stories' => $result]);
}



}

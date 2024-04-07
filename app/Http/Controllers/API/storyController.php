<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class storyController extends Controller
{
    //
   

public function createStory(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $validatedData = $request->validate([
        'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:10000', // 10MB Max
    ]);

    DB::beginTransaction();

    try {
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = time() . '_' . $media->getClientOriginalName();
            $folder = "/{$user->id}/stories";

            Storage::disk('public')->putFileAs($folder, $media, $mediaName);

            $story = new Story();
            $story->userId = $user->id;
            $story->media = $mediaName;
            $story->expiresAt = now()->addDay()->toDateTimeString(); // Expires in 24 hours
            $story->save();

            DB::commit();

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
}

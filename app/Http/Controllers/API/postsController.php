<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\CreatePostRequest;
use App\Models\Posts;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class PostsController extends Controller
{
    public function createPost(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $mediaName = $request->hasFile('media') ? $this->storeMedia($request->file('media'), $user->id) : null;
        $scheduledAt = $request->input('scheduledAt') ? Carbon::parse($request->input('scheduledAt'), $request->header('Timezone')) : now();

        if ($scheduledAt->isPast()) {
            return response()->json(['error' => 'Scheduled time must be in the future.'], 422);
        }

        DB::beginTransaction();
        try {
            $post = Posts::create([
                'userId' => $user->id,
                'caption' => $request->input('content'),
                'media' => $mediaName,
                'tags' => $this->extractTags($request->input('content')),
                'location' => $request->ip(),
                'scheduledAt' => $scheduledAt,
                'postType' => $mediaName ? $this->determineMediaType($mediaName) : null,
                'is_scheduled' => $scheduledAt > now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Post created successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Post creation failed', 'details' => $e->getMessage()], 500);
        }
    }

    private function storeMedia($media, $userId)
    {
        $mediaPath = 'users/' . $userId;
        return $media->store($mediaPath, 'public');
    }

    private function extractTags($content)
    {
        preg_match_all('/#(\w+)/', $content, $matches);
        return implode(',', $matches[1]);
    }

    private function determineMediaType($mediaName)
    {
        $extension = strtolower(Storage::disk('public')->mimeType($mediaName));
        return in_array($extension, ['image/jpeg', 'image/png', 'image/bmp']) ? 'photo' : 'video';
    }
}

<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class followController extends Controller
{

        public function getFollowing()#
        {
            // 1. Check Authentication
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
            // 2. Retrieve Following Data
            $followers = DB::table('follows')
                ->where('user_id', $user->id)
                // ->where('status_target_id', 'recieved')
                ->pluck('target_id'); // Get an array of target IDs
    
            // 3. Fetch User Details
            $followerDetails = [];
            foreach ($followers as $targetId) {
                $targetUser = User::find($targetId);
                if ($targetUser) {
                    $followerDetails[] = [
                        'target_id' => $targetId,
                        'username' => $targetUser->username,
                        'profilePictureURL' => asset('storage/profile/' . $targetUser->profilePicture),
                    ];
                }
            }
    
            // 4. Construct JSON Response
            return response()->json(['following' => $followerDetails]);
        }
    





        public function getFollowers()
        {
            // 1. Check Authentication
            $currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['error' => 'User not found'], 404);
            }
        
            // 2. Retrieve Follower Data
            $followerIds = DB::table('follows')
                ->where('target_id', $currentUser->id)
                // ->where('status_user_id', 'send')
                ->pluck('user_id'); // Get an array of follower IDs
        
            // 3. Fetch User Details
            $followerDetails = [];
            foreach ($followerIds as $followerId) {
                $followerUser = User::find($followerId);
                if ($followerUser) {
                    $followerDetails[] = [
                        'user_id' => $followerId,
                        'username' => $followerUser->username,
                        'profilePictureURL' => asset('storage/profile/' . $followerUser->profilePicture),
                    ];
                }
            }
        
            // 4. Construct JSON Response
            return response()->json(['followers' => $followerDetails]);
        }
    





    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_id' => 'required|exists:Users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $requester = Auth::user();

        if (!$requester) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $targetId = $request->input('target_id');

        if ($requester->id == $targetId) {
            return response()->json(['error' => 'You cannot send a follow request to yourself'], 400);
        }

        DB::beginTransaction();

        try {
            $existingRequest = follow::where('user_id', $requester->id)
                                            ->where('target_id', $targetId)
                                            ->first();

            if ($existingRequest) {
                return response()->json(['error' => 'Follow request already exists'], 409);
            }

            follow::create([
                'user_id' => $requester->id,
                'target_id' => $targetId,
                'status_user_id' => 'send',
            ]);

            DB::commit();

            return response()->json(['message' => 'Followed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while sending the follow request'], 500);
        }
    }


    public function unfollow(Request $request)
    {
        try {
            // Begin a database transaction
            DB::beginTransaction();
    
            // 1. Validate that the target_id is a valid integer
            $request->validate([
                'target_id' => 'required|integer',
            ]);
    
            // 2. Get the current user ID from the token (using Passport)
            $currentUser = Auth::user();
            if (!$currentUser) {
                DB::rollBack(); // Roll back the transaction
                return response()->json(['error' => 'User not authenticated'], 401);
            }
    
            // 3. Find the relationship row
            $relationship = DB::table('follows')
                ->where('user_id', $currentUser->id)
                ->where('target_id', $request->input('target_id'))
                ->first();
    
            if (!$relationship) {
                DB::rollBack(); // Roll back the transaction
                return response()->json(['error' => 'Relationship not found'], 404);
            }
    
            // 4. Delete the relationship row
            DB::table('follows')
                ->where('user_id', $currentUser->id)
                ->where('target_id', $request->input('target_id'))
                ->delete();
    
            // Commit the transaction
            DB::commit();
    
            // 5. Construct JSON Response
            return response()->json(['message' => 'Unfollowed successfully']);
        } catch (\Exception $e) {
            // Something went wrong, handle the exception
            DB::rollBack(); // Roll back the transaction
            return response()->json(['error' => 'An error occurred while unfollowing'], 500);
        }
    }



    // public function acceptRequest(Request $request)
    // {
    //     $requester = Auth::user();

    //     if (!$requester) {
    //         return response()->json(['error' => 'User not authenticated'], 401);
    //     }

    //     $targetId = $request->input('target_id');

    //     DB::beginTransaction();

    //     try {
    //         $follow = Follow::where('target_id', $requester->id)
    //                                       ->where('requester_id', $targetId)
    //                                       ->where('status', 'pending')
    //                                       ->firstOrFail();

    //         $follow->update(['status' => 'accepted']);

    //         DB::commit();

    //         return response()->json(['message' => 'Follow request accepted'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'An error occurred while accepting the follow request'], 500);
    //     }
    // }

    // public function cancelRequest(Request $request)
    // {
    //     $requester = Auth::user();

    //     if (!$requester) {
    //         return response()->json(['error' => 'User not authenticated'], 401);
    //     }

    //     $targetId = $request->input('target_id');

    //     DB::beginTransaction();

    //     try {
    //         $follow = follow::where('target_id', $requester->id)
    //                                       ->where('requester_id', $targetId)
    //                                       ->where('status', 'pending')
    //                                       ->firstOrFail();

    //         $follow->update(['status' => 'cancelled']);

    //         DB::commit();

    //         return response()->json(['message' => 'Follow request cancelled'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'An error occurred while cancelling the follow request'], 500);
    //     }
    // }
}

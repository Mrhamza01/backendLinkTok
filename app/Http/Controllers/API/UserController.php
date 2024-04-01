<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller\hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;



class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:users|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:8|max:30|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            ]);

            if ($validator->fails()) {
                // Return validation errors as JSON response
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Start a database transaction
            DB::beginTransaction();

            // Save user data
            $user = new User();
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password')); // Hash the password
            // Set other fields (if any)

            $user->save();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json(['message' => 'User registered successfully']);
        } catch (\Exception $e) {
            // Handle other exceptions and roll back the transaction
            DB::rollBack();

            // Log the error or return an appropriate error response
            return response()->json(['error' => 'User registration failed'], 500);
        }
    }




    public function login(Request $request){
        /**
         * i will recieve email and password form the frontend in the request 
         * try to validate the request if get error then catch error and send json approporiate response 
         * then authenticate the user i am using laravel sanctum for authentication my project is restfull api base project 
         *  if user is authenticated then genrate a token and send that token in the form with cookie
         * then send a response in json with login susscesfuull
         */

         // Get email and password from the request
   /** * Validate the request (assuming email and password are present) */
  $request->validate([
    'email' => 'required|email',
    'password' => 'required',
  ]);

  // Attempt to authenticate the user using Sanctum
  $user = User::where('email', $request->email)->first();

  if (!$user || !Hash::check($request->password, $user->password)) {
    return response()->json(['error' => 'invalid_credentials'], 401);
  }

  // Generate a token using Sanctum
  $token = $user->createToken($request->device_name)->plainTextToken;

  // Create a cookie for the token with a one-day expiration
  $cookie = cookie('jwt_token', $token, 1440) // 1440 minutes = 1 day
          ->httpOnly(); // Set the HttpOnly flag for security

  // Return a JSON response with a success message
  return response()->json(['message' => 'Login successful'])->withCookie($cookie);



}



}

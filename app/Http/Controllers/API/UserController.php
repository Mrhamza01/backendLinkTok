<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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




    


public function login(Request $request)
{
    // Validate the request
    $request->validate([
        'email' => 'required|string', // Assuming you're using 'username' field
        'password' => 'required|string',
    ]);

    // Attempt to authenticate the user
    if (!Auth::attempt($request->only('email', 'password'))) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Get the currently authenticated user
    $user = Auth::user();

    // Create a token for the user
    $token = $user->createToken('auth_token')->accessToken;


    // Set the token as a cookie (optional)
    $cookie = Cookie::make('auth_token', $token, 60 * 24 * 7); // 1 week

    // Return a successful response with the token
    return response([
        'message' => 'Login successful',
        'redirect' => '/',
    ])->withCookie($cookie);
}


























}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid Form Input'], 400);
        }

        // Create new user with validated data
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Create a wallet for the user with initial balance
        $user->wallet()->create([
            'balance' => 0.0,
        ]);

        // Generate JWT token for the registered user
        $token = JWTAuth::fromUser($user);

        // Return success response with JWT token
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    public function getUser()
    {
        return response()->json(Auth::user(), 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid login credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Cound not create token'], 500);
        }
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'],  200);
    }
    public function updateUser(Request $request)
    {
        $user = Auth::user();
        $userID = Auth::user()->id;
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', Rule::unique('users')->ignore($userID)
            ],
            'password' => 'required|string|min:8',

        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        $userToUpdate = User::find($userID);
        $userToUpdate->update($validatedData);

        return response()->json([
            'message' => "User updated successfully"
        ], 201);
    }
    public function delete(Request $request)
    {
        $userID = Auth::user()->id;

        $userToDelete = User::find($userID);

        if (!$userToDelete) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $userToDelete->delete();
        return response()->json([
            'message' => "User deleted successfully"
        ], 200);
    }
}

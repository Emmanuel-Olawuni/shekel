<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['error' => 'Invalid Form Input'], 400);
        } else {
            $user = User::create($request->all());
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 200);
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
                return response()->json(['message' => 'Invalid login details'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Cound not create token'], 500);
        }
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'],  200);
    }
    public function updateUser(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => `required|string|email|max:255|unique:users` . $id,
            'password' => 'required|string|min:8',

        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        $userToUpdate = User::find($id);
        $userToUpdate->update($validatedData);

        return response()->json([
            'message' => "User updated successfully"
        ], 200);
    }
    public function delete(Request $request, $id)
    {

        $userToDelete = User::find($id);

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

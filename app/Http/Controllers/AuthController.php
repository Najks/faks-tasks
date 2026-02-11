<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //

    public function register(Request $request)
    {
        $data = $request->validate([
            "username" => "required|string|max:255",
            "email" => "required|email",
            "password" => "required|string|min:6",
            "name" => "required|string|min:3",
        ]);

        if (User::where("email", $data["email"])->orWhere("username", $data["username"])->exists()) {
            return response()->json([
                "message" => "User already exists",
                "errors" => [
                    "email" => ["The email or username is already taken."],
                ],
            ], 409);
        }

        $user = User::create([
            "username" => $data["username"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
            "role" => "user",
            "name" => $data["name"]
        ]);

        $token = $user->createToken("api-token")->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token,
        ], 201);

    }

    public function login(Request $request){
        $data = $request->validate([
            "email" => "required|email",
            "password" => "required|string"
        ]);

        $user = User::where("email", $data["email"])->first();

        if (! $user || ! Hash::check($data["password"], $user->password)){
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }
        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "user" => $user,
            "token" => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = $user->currentAccessToken();

        if (! $token) {
            return response()->json(['message' => 'Invalid or missing token'], 401);
        }

        $token->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}

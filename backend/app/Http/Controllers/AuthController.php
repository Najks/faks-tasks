<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            "username" => "sometimes|required|string|max:255",
            "email" => "sometimes|required|email",
            "password" => "sometimes|required|string|min:6",
            "name" => "sometimes|required|string|min:3",
        ]);

        if (isset($data["email"]) && User::where("email", $data["email"])->where("id", "<>", $user->id)->exists()) {
            return response()->json([
                "message" => "Email already in use",
                "errors" => [
                    "email" => ["The email is already taken."],
                ],
            ], 409);
        }

        if (isset($data["username"]) && User::where("username", $data["username"])->where("id", "<>", $user->id)->exists()) {
            return response()->json([
                "message" => "Username already in use",
                "errors" => [
                    "username" => ["The username is already taken."],
                ],
            ], 409);
        }

        $user->fill($data);

        if (isset($data["password"])) {
            $user->password = Hash::make($data["password"]);
        }

        $user->save();

        return response()->json($user);
    }

    public function requestPasswordReset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'message' => 'Password reset token generated.',
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $record = DB::table('password_resets')->where('email', $data['email'])->first();

        if (! $record || ! Hash::check($data['token'], $record->token)) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'Token expired'], 422);
        }

        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_resets')->where('email', $data['email'])->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function resetPasswordSimple(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


/**
 * @OA\Info(title="Authentication API", version="1.0")
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer"
 * )
 * @OA\PathItem(path="/api")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully")
     * )
     */
    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::create($fields);

        $token = $user->createToken($request->name);

        $data = [
            "message" => "Register succesfull",
            "user" => $user,
            "token" => $token->plainTextToken,
        ];

        return response()->json($data, 201);
    }
    

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Login successful")
     * )
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Username or password is invalid"
            ], 404);
        }

        $token = $user->createToken($user->name);

        $data = [
            "message" => "Login succesful",
            "user" => $user,
            "token" => $token->plainTextToken,
        ];

        return response()->json($data, 201)->cookie("ACCESS_TOKEN", $token->plainTextToken, 1440, '/', '/', true, true);
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request) {
        $request->user()->tokens()->delete();

        return response()->json([
            "message" => "Logout successful"
        ], 200);
    }
}

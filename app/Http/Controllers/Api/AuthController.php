<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Generate a signed stateless bearer token compatible with serverless multi-instance execution.
     */
    protected function generateToken(User $user): string
    {
        $payload = base64_encode(json_encode([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'exp' => time() + (86400 * 30),
        ]));
        $signature = hash_hmac('sha256', $payload, (string) config('app.key'));

        return $payload . '.' . $signature;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $token = $this->generateToken($user);

        return response()->json([
            'message' => 'Регистрация успешна',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный адрес электронной почты или пароль.'],
            ]);
        }

        $token = $this->generateToken($user);

        return response()->json([
            'message' => 'Успешная авторизация',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()?->currentAccessToken()?->delete();
        } catch (\Throwable $e) {}

        return response()->json([
            'message' => 'Сессия завершена',
        ]);
    }
}

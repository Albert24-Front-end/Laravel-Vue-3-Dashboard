<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;

class SanctumTokenFromQuery
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenString = $request->bearerToken();
        if (!$tokenString) {
            $authHeader = $request->header('Authorization') ?: ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $tokenString = trim($matches[1]);
            }
        }
        if (!$tokenString) {
            $tokenString = $request->query('token') ?: $request->query('api_token');
        }

        if ($tokenString) {
            // 1. Check if token is a signed stateless token
            if (str_contains($tokenString, '.')) {
                $parts = explode('.', $tokenString, 2);
                if (count($parts) === 2) {
                    [$payloadBase64, $signature] = $parts;
                    $expectedSig = hash_hmac('sha256', $payloadBase64, (string) config('app.key'));
                    if (hash_equals($expectedSig, $signature)) {
                        $data = json_decode(base64_decode($payloadBase64), true);
                        if (is_array($data) && isset($data['email']) && ($data['exp'] ?? 0) > time()) {
                            $user = User::where('email', $data['email'])->first();
                            if (!$user) {
                                $user = User::firstOrCreate(
                                    ['email' => $data['email']],
                                    [
                                        'name' => $data['name'] ?? 'Сотрудник компании',
                                        'password' => Hash::make(Str::random(32)),
                                    ]
                                );
                            }
                            if ($user) {
                                $user->withAccessToken(new TransientToken);
                                Auth::setUser($user);
                                $request->setUserResolver(fn () => $user);
                                return $next($request);
                            }
                        }
                    }
                }
            }

            // 2. Otherwise fallback to standard Sanctum database token
            $accessToken = PersonalAccessToken::findToken($tokenString);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
                $user->withAccessToken($accessToken);
                Auth::setUser($user);
                $request->setUserResolver(fn () => $user);
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}

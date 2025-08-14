<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Exception;

class FirebaseAuthMiddleware
{
    private $auth;

    public function __construct()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('services.firebase.credentials'))
                ->withProjectId(config('services.firebase.project_id'));
            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            \Log::error('Firebase Auth initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Authorization header missing or invalid'
            ], 401);
        }

        try {
            // Bearerトークンを抽出
            $token = substr($authHeader, 7);

            // Firebase認証トークンを検証
            $verifiedIdToken = $this->auth->verifyIdToken($token);

            // Firebaseユーザー情報を取得
            $firebaseUser = $this->auth->getUser($verifiedIdToken->claims()->get('sub'));

            // リクエストにユーザー情報を追加
            $user = (object) [
                'id' => $firebaseUser->uid,
                'email' => $firebaseUser->email,
                'name' => $firebaseUser->displayName,
                'email_verified' => $firebaseUser->emailVerified,
            ];

            $request->attributes->set('firebase_user', $user);

            return $next($request);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid or expired token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
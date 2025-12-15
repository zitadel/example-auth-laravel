<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that ensures the user is authenticated before accessing protected routes.
 *
 * Retrieves the current session and validates that a user is present. If authentication
 * fails, the client is redirected to the sign-in page with the original URL preserved
 * in the callbackUrl query parameter. Handles automatic token refresh if the token is expired.
 */
readonly class RequireAuth
{
    /**
     * @param AuthService $authService
     */
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('zitadel_user');
        session('access_token');
        $refreshToken = session('refresh_token');
        $expiresAt = session('expires_at');

        if (!$user) {
            $callbackUrl = urlencode($request->fullUrl());
            return redirect()->route('auth.signin', ['callbackUrl' => $callbackUrl]);
        }

        if ($this->authService->isTokenExpired($expiresAt) && $refreshToken) {
            $refreshed = $this->authService->refreshAccessToken($refreshToken);

            if ($refreshed) {
                session([
                    'access_token' => $refreshed['access_token'],
                    'refresh_token' => $refreshed['refresh_token'],
                    'expires_at' => $refreshed['expires_at'],
                ]);
            } else {
                session()->flush();
                $callbackUrl = urlencode($request->fullUrl());
                return redirect()->route('auth.signin', ['callbackUrl' => $callbackUrl]);
            }
        }

        return $next($request);
    }
}

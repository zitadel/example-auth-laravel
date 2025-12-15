<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Random\RandomException;

/**
 * Manages ZITADEL authentication tokens including automatic refresh.
 *
 * Handles token storage in session, expiry detection, and seamless token
 * refresh using OAuth2 refresh token grant. Ensures users maintain active
 * sessions without re-authentication.
 */
class AuthService
{
    /**
     * Refreshes an expired access token using the refresh token.
     *
     * @param string $refreshToken
     * @return array<string, mixed>|null
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            $response = Http::asForm()->post(config('zitadel.domain') . '/oauth/v2/token', [
                'client_id' => config('zitadel.client_id'),
                'client_secret' => config('zitadel.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                'expires_at' => isset($data['expires_in'])
                    ? time() + (int)$data['expires_in']
                    : time() + 3600,
            ];
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Checks if the access token has expired.
     *
     * @param int|null $expiresAt
     * @return bool
     */
    public function isTokenExpired(?int $expiresAt): bool
    {
        if ($expiresAt === null) {
            return true;
        }

        return time() >= $expiresAt;
    }

    /**
     * Builds the ZITADEL logout URL with state parameter for CSRF protection.
     *
     * @param string $idToken
     * @return array{url: string, state: string}
     * @throws RandomException
     */
    public function buildLogoutUrl(string $idToken): array
    {
        $state = bin2hex(random_bytes(16));

        $params = http_build_query([
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => config('zitadel.post_logout_url'),
            'state' => $state,
        ]);

        return [
            'url' => config('zitadel.domain') . '/oidc/v1/end_session?' . $params,
            'state' => $state,
        ];
    }
}

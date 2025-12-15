<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Auth;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Override;

/**
 * Custom ZITADEL OAuth2/OIDC provider for Laravel Socialite.
 *
 * This provider extends the standard AbstractProvider to handle ZITADEL's
 * specific requirements, such as Basic Auth for token exchange and strict
 * OIDC scope handling.
 *
 * @see AbstractProvider
 */
class ZitadelProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base domain for the ZITADEL instance.
     */
    protected string $domain;

    /**
     * Create a new provider instance.
     *
     * Configures the provider to use PKCE and sets ZITADEL-specific defaults.
     *
     * @param Request $request
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array<string, mixed> $guzzle
     * @return void
     */
    public function __construct(
        Request $request,
        string  $clientId,
        string  $clientSecret,
        string  $redirectUrl,
        array   $guzzle = []
    ) {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        $this->domain = rtrim((string)config('zitadel.domain'), '/');
        $this->scopeSeparator = ' ';
        $this->usesPKCE = true;
        $this->scopes = config('zitadel.scopes', []);
    }

    /**
     * Get the access token response for the given code.
     *
     * OVERRIDE: This method is overridden to support ZITADEL's requirement for
     * Basic Authentication headers during token exchange. The default
     * implementation places credentials in the POST body, which ZITADEL rejects
     * with 400 Bad Request.
     *
     * @param string $code
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    #[Override]
    public function getAccessTokenResponse($code): array
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'code_verifier' => $this->request->session()->pull('code_verifier'),
        ];

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(
                    $this->clientId . ':' . $this->clientSecret
                ),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query($fields),
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    #[Override]
    protected function getTokenUrl(): string
    {
        return "$this->domain/oauth/v2/token";
    }

    /**
     * Get the current scopes.
     *
     * OVERRIDE: Ensures the 'openid' scope is always present. Without this,
     * ZITADEL will not return an ID Token, which breaks federated logout and
     * user identification features.
     *
     * @return array<int, string>
     */
    #[Override]
    public function getScopes(): array
    {
        $scopes = parent::getScopes();

        if (!in_array('openid', $scopes)) {
            $scopes[] = 'openid';
        }

        return array_unique(array_merge($scopes, ['profile', 'email']));
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    #[Override]
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            "$this->domain/oauth/v2/authorize",
            $state
        );
    }

    /**
     * Get the raw user for the given access token.
     *
     * Calls the OIDC UserInfo endpoint to retrieve user profile data.
     *
     * @param string $token
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    #[Override]
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            "$this->domain/oidc/v1/userinfo",
            [
                'headers' => [
                    'Authorization' => "Bearer $token",
                ],
            ]
        );

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * Maps standard OIDC claims (sub, name, picture) to Socialite properties.
     *
     * @param array<string, mixed> $user
     * @return User
     */
    #[Override]
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['sub'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,
        ]);
    }

    /**
     * Create a user instance from the given data.
     *
     * OVERRIDE: Attaches the raw token response (containing id_token) to the
     * user object. This allows the controller to access the ID Token via
     * the accessTokenResponseBody property.
     *
     * @param array<string, mixed> $response
     * @param array<string, mixed> $user
     * @return User
     * @noinspection PhpDynamicFieldDeclarationInspection
     */
    #[Override]
    protected function userInstance(array $response, array $user): User
    {
        $userObject = parent::userInstance($response, $user);

        /** @phpstan-ignore-next-line */
        $userObject->accessTokenResponseBody = $response;

        return $userObject;
    }
}

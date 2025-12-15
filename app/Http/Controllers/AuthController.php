<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\MessageService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Random\RandomException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

/**
 * Handles OAuth/OIDC authentication flows with ZITADEL.
 *
 * Manages sign-in, callback, logout, and user info endpoints.
 */
class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param AuthService $authService
     * @param MessageService $messageService
     */
    public function __construct(
        private readonly AuthService $authService,
        private readonly MessageService $messageService
    ) {
    }

    /**
     * Display the sign-in page.
     *
     * @param Request $request
     * @return View
     */
    public function showSignin(Request $request): View
    {
        $error = $request->query('error');
        $message = $this->messageService->getMessage($error, 'signin-error');

        return view('auth.signin', [
            'providers' => [
                [
                    'id' => 'zitadel',
                    'name' => 'ZITADEL',
                    'signinUrl' => route('auth.signin.provider', ['provider' => 'zitadel']),
                ],
            ],
            'callbackUrl' => $request->query('callbackUrl'),
            'message' => $error ? $message : null,
        ]);
    }

    /**
     * Redirect to the OAuth provider.
     *
     * @param string $provider
     * @return SymfonyRedirectResponse
     */
    public function redirectToProvider(string $provider): SymfonyRedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback.
     *
     * @param string $provider
     * @return RedirectResponse
     * @noinspection PhpUndefinedFieldInspection
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        try {
            /** @var User $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->user();

            /** @phpstan-ignore-next-line */
            $tokenResponse = $socialiteUser->accessTokenResponseBody;

            if (!isset($tokenResponse['id_token'])) {
                Log::critical('ZITADEL did not return an id_token. Check scopes.');
                return redirect()->route('auth.error', ['error' => 'missing_id_token']);
            }

            session()->put([
                'zitadel_user' => $socialiteUser->user,
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'id_token' => $tokenResponse['id_token'],
                'expires_at' => isset($tokenResponse['expires_in'])
                    ? time() + (int) $tokenResponse['expires_in']
                    : time() + 3600,
            ]);

            session()->save();

            return redirect()->route('profile');
        } catch (ClientException $e) {
            Log::error('ZITADEL provider rejected request', [
                'status' => $e->getResponse()->getStatusCode(),
                'body' => $e->getResponse()->getBody()->getContents(),
            ]);

            return redirect()->route('auth.error', ['error' => 'provider_rejection']);
        } catch (Exception $e) {
            Log::error('Unexpected error during authentication', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('auth.error', ['error' => 'generic_error']);
        }
    }

    /**
     * Display the authentication error page.
     *
     * @param Request $request
     * @return View
     */
    public function showError(Request $request): View
    {
        $error = $request->query('error');
        $message = $this->messageService->getMessage($error, 'auth-error');

        return view('auth.error', $message);
    }

    /**
     * Initiate a logout process.
     *
     * @return RedirectResponse
     * @throws RandomException
     */
    public function logout(): RedirectResponse
    {
        $idToken = session('id_token');

        if (!$idToken) {
            return redirect()->route('home')->with(
                'error',
                'No valid session or ID token found'
            );
        }

        $logoutData = $this->authService->buildLogoutUrl($idToken);

        session(['logout_state' => $logoutData['state']]);

        return redirect($logoutData['url']);
    }

    /**
     * Handle logout callback from ZITADEL.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logoutCallback(Request $request): RedirectResponse
    {
        $state = $request->query('state');
        $logoutState = session('logout_state');

        if ($state && $logoutState && $state === $logoutState) {
            session()->flush();
            return redirect()->route('auth.logout.success');
        }

        $reason = urlencode('Invalid or missing state parameter.');
        return redirect()->route('auth.logout.error', ['reason' => $reason]);
    }

    /**
     * Display the logout success page.
     *
     * @return View
     */
    public function logoutSuccess(): View
    {
        return view('auth.logout.success');
    }

    /**
     * Display logout error page.
     *
     * @param Request $request
     * @return View
     */
    public function logoutError(Request $request): View
    {
        return view('auth.logout.error', [
            'reason' => $request->query('reason', 'An unknown error occurred.'),
        ]);
    }

    /**
     * Fetch user info from ZITADEL.
     *
     * @return JsonResponse
     */
    public function userInfo(): JsonResponse
    {
        $accessToken = session('access_token');

        if (!$accessToken) {
            return response()->json(
                ['error' => 'No access token available'],
                401
            );
        }

        try {
            $response = Http::withToken($accessToken)
                ->get(config('zitadel.domain') . '/oidc/v1/userinfo');

            if (!$response->successful()) {
                return response()->json(
                    ['error' => 'UserInfo API error: ' . $response->status()],
                    $response->status()
                );
            }

            return response()->json($response->json());
        } catch (Exception) {
            return response()->json(
                ['error' => 'Failed to fetch user info'],
                500
            );
        }
    }
}

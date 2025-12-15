<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Handles the user profile page display.
 */
class ProfileController extends Controller
{
    /**
     * Display the profile page.
     *
     * @return View
     */
    public function show(): View
    {
        $sessionData = [
            'user' => session('zitadel_user'),
            'idToken' => session('id_token'),
            'accessToken' => session('access_token'),
            'expiresAt' => session('expires_at'),
        ];

        return view('profile', [
            'userJson' => json_encode($sessionData, JSON_PRETTY_PRINT),
        ]);
    }
}

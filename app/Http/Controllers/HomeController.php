<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Handles the application home page display.
 */
class HomeController extends Controller
{
    /**
     * Display the home page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('home', [
            'isAuthenticated' => session()->has('zitadel_user'),
            'loginUrl' => route('auth.signin.provider', ['provider' => 'zitadel']),
        ]);
    }
}

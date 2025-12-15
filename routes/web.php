<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RequireAuth;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/static/{path}', function ($path) {
        $file = public_path($path);
        if (!file_exists($file)) {
            abort(404);
        }
        return response()->file($file);
    })->where('path', '.*');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/auth/signin', [AuthController::class, 'showSignin'])->name('auth.signin');
Route::post('/auth/signin/{provider}', [AuthController::class, 'redirectToProvider'])->name('auth.signin.provider');
Route::get('/auth/callback/{provider}', [AuthController::class, 'handleProviderCallback'])->name('auth.callback');
Route::get('/auth/error', [AuthController::class, 'showError'])->name('auth.error');

Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/logout/callback', [AuthController::class, 'logoutCallback'])->name('auth.logout.callback');
Route::get('/auth/logout/success', [AuthController::class, 'logoutSuccess'])->name('auth.logout.success');
Route::get('/auth/logout/error', [AuthController::class, 'logoutError'])->name('auth.logout.error');

Route::middleware([RequireAuth::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/auth/userinfo', [AuthController::class, 'userInfo'])->name('auth.userinfo');
});

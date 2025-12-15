<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\ZitadelProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Override;

/**
 * Service provider for registering custom Socialite drivers.
 */
class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Socialite::extend('zitadel', function ($app) {
            $config = $app['config']['zitadel'];

            return new ZitadelProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['callback_url']
            );
        });
    }
}

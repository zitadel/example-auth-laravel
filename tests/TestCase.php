<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Override;
use Random\RandomException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Boot the application.
     * Implements the logic normally found in the missing CreatesApplication trait.
     */
    #[Override]
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Set up the test environment.
     * @throws RandomException
     */
    #[Override]
    protected function setUp(): void
    {
        putenv('APP_KEY=base64:' . base64_encode(random_bytes(32)));

        putenv("PORT=3000");
        putenv("SESSION_DURATION=3600");
        putenv("SESSION_SECRET=test-secret-key-for-pytest");

        putenv("ZITADEL_DOMAIN=https://test.us1.zitadel.cloud");
        putenv("ZITADEL_CLIENT_ID=mock-client-id");
        putenv("ZITADEL_CLIENT_SECRET=mock-client-secret");
        putenv("ZITADEL_CALLBACK_URL=http://localhost:3000/auth/callback");
        putenv("ZITADEL_POST_LOGOUT_URL=http://localhost:3000/auth/logout/callback");

        parent::setUp();
    }
}

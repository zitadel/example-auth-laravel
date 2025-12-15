<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Basic smoke tests for application.
 */
class AppTest extends TestCase
{
    /**
     * test_app_starts
     */
    public function test_app_starts(): void
    {
        $this->assertNotNull($this->app);
    }

    /**
     * test_home_page_loads
     */
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * test_signin_page_loads
     * Matches Flask test exactly: GET /auth/signin
     */
    public function test_signin_page_loads(): void
    {
        $response = $this->get('/auth/signin');
        $response->assertStatus(200);
    }

    /**
     * test_profile_redirects_when_unauthenticated
     * Same behavior as Flask—redirect if not logged in.
     */
    public function test_profile_redirects_when_unauthenticated(): void
    {
        $response = $this->get('/profile');

        $response->assertStatus(302);
    }
}

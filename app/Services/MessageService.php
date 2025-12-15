<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Provides user-friendly error messages for authentication flows.
 *
 * Translates technical OAuth/OIDC error codes into readable messages
 * for signin errors and general authentication errors.
 */
class MessageService
{
    /**
     * Retrieves error message and heading for display.
     *
     * @param string|null $errorCode
     * @param string $category
     * @return array{heading: string, message: string}
     */
    public function getMessage(?string $errorCode, string $category): array
    {
        $normalized = strtolower($errorCode ?? 'default');

        if ($category === 'signin-error') {
            return $this->getSigninErrorMessage($normalized);
        }

        return $this->getAuthErrorMessage($normalized);
    }

    /**
     * @param string $normalized
     * @return array{heading: string, message: string}
     */
    private function getSigninErrorMessage(string $normalized): array
    {
        return match ($normalized) {
            'signin', 'oauthsignin', 'oauthcallback', 'oauthcreateaccount', 'emailcreateaccount', 'callback' => [
                'heading' => 'Sign-in Failed',
                'message' => 'Try signing in with a different account.',
            ],
            'oauthaccountnotlinked' => [
                'heading' => 'Account Not Linked',
                'message' => 'To confirm your identity, sign in with the same account you used originally.',
            ],
            'emailsignin' => [
                'heading' => 'Email Not Sent',
                'message' => 'The email could not be sent.',
            ],
            'credentialssignin' => [
                'heading' => 'Sign-in Failed',
                'message' => 'Sign in failed. Check the details you provided are correct.',
            ],
            'sessionrequired' => [
                'heading' => 'Sign-in Required',
                'message' => 'Please sign in to access this page.',
            ],
            default => [
                'heading' => 'Unable to Sign in',
                'message' => 'An unexpected error occurred during sign-in. Please try again.',
            ],
        };
    }

    /**
     * @param string $normalized
     * @return array{heading: string, message: string}
     */
    private function getAuthErrorMessage(string $normalized): array
    {
        return match ($normalized) {
            'configuration' => [
                'heading' => 'Server Error',
                'message' => 'There is a problem with the server configuration. Check the server logs for more information.',
            ],
            'accessdenied' => [
                'heading' => 'Access Denied',
                'message' => 'You do not have permission to sign in.',
            ],
            'verification' => [
                'heading' => 'Sign-in Link Invalid',
                'message' => 'The sign-in link is no longer valid. It may have been used already or it may have expired.',
            ],
            default => [
                'heading' => 'Authentication Error',
                'message' => 'An unexpected error occurred during authentication. Please try again.',
            ],
        };
    }
}

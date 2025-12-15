# Laravel with ZITADEL

Laravel is a web application framework with expressive, elegant syntax. This guide demonstrates how to use Laravel
Socialite with a custom ZITADEL provider to implement secure login with ZITADEL.

We'll be using the **OpenID Connect (OIDC)** protocol with the **Authorization Code Flow + PKCE**. This is the
industry-best practice for security, ensuring that the login process is safe from start to finish.

## Example Application

The example repository includes a complete Laravel application, ready to run, that demonstrates how to integrate ZITADEL
for user authentication.

This example application showcases a typical web app authentication pattern: users start on a public landing page, click
a login button to authenticate with ZITADEL, and are then redirected to a protected profile page displaying their user
information. The app also includes secure logout functionality that clears the session and redirects users back to
ZITADEL's logout endpoint.

### Prerequisites

Before you begin, ensure you have the following:

#### System Requirements

- PHP 8.3 or later
- Composer package manager

#### Account Setup

You'll need a ZITADEL account and application configured. Follow the ZITADEL documentation on creating applications to
set up your account and create a Web application with Authorization Code + PKCE flow.

> **Important:** Configure the following URLs in your ZITADEL application settings:
>
> - **Redirect URIs:** Add `http://localhost:8000/auth/callback` (for development)
> - **Post Logout Redirect URIs:** Add `http://localhost:8000/auth/logout/callback` (for development)
>
> These URLs must exactly match what your Laravel application uses. For production, add your production URLs.

### Configuration

To run the application, you first need to copy the `.env.example` file to a new file named `.env` and fill in your
ZITADEL application credentials.

```bash
cp .env.example .env
```

Then update the following values in `.env`:

```dotenv
# Port number where your Laravel server will listen
PORT=8000

# Session timeout in seconds
SESSION_DURATION=3600

# Secret key used to sign session cookies
SESSION_SECRET="your-very-secret-and-strong-session-key"

# Your ZITADEL instance domain URL
ZITADEL_DOMAIN="https://your-zitadel-domain"

# Application Client ID from ZITADEL
ZITADEL_CLIENT_ID="your-client-id"

# Client secret (can be randomly generated for PKCE)
ZITADEL_CLIENT_SECRET="your-randomly-generated-client-secret"

# OAuth callback URL
ZITADEL_CALLBACK_URL="http://localhost:8000/auth/callback"

# Post logout redirect URL
ZITADEL_POST_LOGOUT_URL="http://localhost:8000/auth/logout/callback"
```

### Installation and Running

Follow these steps to get the application running:

```bash
# 1. Install dependencies
composer install

# 2. Generate application key
php artisan key:generate

# 3. Create storage directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs

# 4. Set permissions
chmod -R 775 storage bootstrap/cache

# 5. Start the development server
php artisan serve --port=8000
```

The application will now be running at `http://localhost:8000`.

## Key Features

### PKCE Authentication Flow

The application implements the secure Authorization Code Flow with PKCE (Proof Key for Code Exchange), which is the
recommended approach for modern web applications.

### Session Management

Built-in session management with Laravel handles user authentication state across your application, with automatic token
refresh and secure session storage.

### Route Protection

Protected routes automatically redirect unauthenticated users to the login flow via the `RequireAuth` middleware,
ensuring sensitive areas of your application remain secure.

### Logout Flow

Complete logout implementation that properly terminates both the local session and the ZITADEL session, with proper
redirect handling and CSRF protection.

### Automatic Token Refresh

The middleware automatically detects expired access tokens and refreshes them using the refresh token, maintaining
seamless user sessions without re-authentication.

## Resources

- **Laravel Documentation:** https://laravel.com/docs
- **Laravel Socialite:** https://laravel.com/docs/socialite
- **ZITADEL Documentation:** https://zitadel.com/docs

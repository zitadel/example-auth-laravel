<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zitadel PKCE Demo</title>
    <!--suppress JSUnresolvedLibraryURL -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="icon" href="{{ asset('static/app-logo.svg') }}" type="image/svg+xml">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
@yield('content')
</body>
</html>

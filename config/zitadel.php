<?php

declare(strict_types=1);

return [
    'domain' => env('ZITADEL_DOMAIN'),
    'client_id' => env('ZITADEL_CLIENT_ID'),
    'client_secret' => env('ZITADEL_CLIENT_SECRET'),
    'post_logout_url' => env('ZITADEL_POST_LOGOUT_URL'),

    'scopes' => [
        'openid',
        'profile',
        'email',
        'offline_access',
        'urn:zitadel:iam:user:metadata',
        'urn:zitadel:iam:user:resourceowner',
        'urn:zitadel:iam:org:projects:roles',
    ],
];

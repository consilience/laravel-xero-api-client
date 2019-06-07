<?php

return [
    // The default app settings.

    'default' => 'app1',

    // Application name, user agent sent to Xero.

    'application_name' => 'Laravel Xero',

    // Guard time in seconds.
    // 300 secons = 5 minutes.

    'gaurd_time_seconds' => 300,

    // List of applications.

    'apps' => [
        'app1' => [
            // One of "partner", "public", "private".
            // Only "partner" supported at this time.

            'type' => 'partner',

            // The authentication type.
            // Only "oauth1" supported at this time.

            'auth_type' => 'oauth1',

            // Settings for authentication.

            'consumer_key' => env('XERO_APP1_CONSUMER_KEY'),
            'consumer_secret' => env('XERO_APP1_CONSUMER_SECRET'),

            // See Consilience\XeroApi\Client\AbstractClient::SIGNATURE_METHOD_*

            'signature_method' => Consilience\XeroApi\Client\AbstractClient::SIGNATURE_METHOD_RSA,

            'private_key_file' => env('XERO_APP1_PRIVATE_KEY_FILE', 'certs/xero/app1-private.pem'),
            'private_key_passphrase' => env('XERO_APP1_PRIVATE_KEY_PASSPHRASE', ''),
        ],
    ]
];

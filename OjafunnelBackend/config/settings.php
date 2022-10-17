<?php

return [
    'auth'=> [
        'email_verification_token_expiration' => '60',
        'withdrawal_verification_token_expiration' => '60'
    ],
    'customer_support' => [
        'domain' => env('APP_DOMAIN', 'ojafunnel.com'),
        'logo' => env('APP_lOGO', 'https://res.cloudinary.com/greenmouse-tech/image/upload/v1660217514/OjaFunnel-Images/Logo_s0wfpp.png'),
        'email' => env('APP_MAIL', 'support@ojafunnel.com'),
        'phone' => env('APP_PHONE', '234 9000'),
    ],
];


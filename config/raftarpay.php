<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Gateway
    |--------------------------------------------------------------------------
    |
    | The gateway used when you call Payment::charge() without naming one.
    | Supported: "jazzcash", "easypaisa", "kuickpay", "faysal", "meezan".
    |
    */

    'default' => env('RAFTARPAY_GATEWAY', 'jazzcash'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | "sandbox" points every gateway at its test endpoints, "production" at
    | the live ones. Keep this on sandbox until you have gone live.
    |
    */

    'environment' => env('RAFTARPAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | RaftarPay registers a return URL and a server-to-server callback URL for
    | each gateway. Customers come back to {prefix}/{gateway}/return and the
    | bank posts its notification to {prefix}/{gateway}/callback.
    |
    */

    'routes' => [
        'enabled'    => true,
        'prefix'     => 'raftarpay',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Persistence
    |--------------------------------------------------------------------------
    |
    | When enabled every charge/verify/callback is stored in the
    | raftarpay_transactions table for auditing and reconciliation.
    |
    */

    'logging' => [
        'enabled' => true,
        'table'   => 'raftarpay_transactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    |
    | "mode" is the card-security flow where applicable:
    |   "3d" => 3D-Secure / OTP verification (default, recommended)
    |   "2d" => direct charge without OTP (only if your merchant is approved)
    |
    */

    'gateways' => [

        'jazzcash' => [
            'merchant_id'    => env('JAZZCASH_MERCHANT_ID'),
            'password'       => env('JAZZCASH_PASSWORD'),
            'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
            'mode'           => env('JAZZCASH_MODE', '3d'),
            'currency'       => 'PKR',
            'language'       => 'EN',
        ],

        'easypaisa' => [
            'store_id'      => env('EASYPAISA_STORE_ID'),
            'hash_key'      => env('EASYPAISA_HASH_KEY'),
            'account_num'   => env('EASYPAISA_ACCOUNT_NUM'),
            'mode'          => env('EASYPAISA_MODE', '3d'),
            'currency'      => 'PKR',
        ],

        'kuickpay' => [
            'merchant_id'  => env('KUICKPAY_MERCHANT_ID'),
            'auth_key'     => env('KUICKPAY_AUTH_KEY'),
            'hash_key'     => env('KUICKPAY_HASH_KEY'),
            'currency'     => 'PKR',
        ],

        'faysal' => [
            'merchant_id'  => env('FAYSAL_MERCHANT_ID'),
            'merchant_pwd' => env('FAYSAL_MERCHANT_PASSWORD'),
            'hash_key'     => env('FAYSAL_HASH_KEY'),
            'mode'         => env('FAYSAL_MODE', '3d'),
            'currency'     => 'PKR',
        ],

        'meezan' => [
            'merchant_id'  => env('MEEZAN_MERCHANT_ID'),
            'merchant_pwd' => env('MEEZAN_MERCHANT_PASSWORD'),
            'hash_key'     => env('MEEZAN_HASH_KEY'),
            'mode'         => env('MEEZAN_MODE', '3d'),
            'currency'     => 'PKR',
        ],

    ],

];

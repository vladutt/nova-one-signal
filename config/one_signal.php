<?php

return [
    'api_key' => env('ONE_SIGNAL_API_KEY'),
    'app_id' => env('ONE_SIGNAL_APP_ID'),
    'model' => App\User::class,
    'name' => 'name',
    'avatar' => 'avatar',
    'recipients_fields' => [
        'external_user_id',
        'id',
        'language',
        'device_model',
        'created_at',
        'last_active',
    ],
    'locales' => [
        'en' => 'English',
        'fr' => 'French',
    ],
    'fallback_locale' => 'en',
];

[![Latest Stable Version](https://poser.pugx.org/yassi/nova-one-signal/v/stable)](https://packagist.org/packages/yassi/nova-one-signal) [![Total Downloads](https://poser.pugx.org/yassi/nova-one-signal/downloads)](https://packagist.org/packages/yassi/nova-one-signal) [![Latest Unstable Version](https://poser.pugx.org/yassi/nova-one-signal/v/unstable)](https://packagist.org/packages/yassi/nova-one-signal) [![License](https://poser.pugx.org/yassi/nova-one-signal/license)](https://packagist.org/packages/yassi/nova-one-signal) [![Monthly Downloads](https://poser.pugx.org/yassi/nova-one-signal/d/monthly)](https://packagist.org/packages/yassi/nova-one-signal) [![Daily Downloads](https://poser.pugx.org/yassi/nova-one-signal/d/daily)](https://packagist.org/packages/yassi/nova-one-signal)

# Nova One Signal

This package allows you to use the [OneSignal](https://onesignal.com/) API to send notifications from your Nova admin dashboard.

# Installation

```bash
composer require yassi/nova-one-signal
php artisan vendor:publish --provider="Yassi\NovaOneSignal\ToolServiceProvider"
```

# Configuration

You can edit the configuration file as follows:

```php
<?php

return [
    'api_key' => env('ONE_SIGNAL_API_KEY'),
    'app_id' => env('ONE_SIGNAL_APP_ID'),
    'model' => ClassToUseWithExternalUserID::class,
    'name' => 'user_name_key',
    'avatar' => 'user_avatar_key',
    'locales' => [
        'en' => 'English',
        'fr' => 'Français'
    ],
    'fallback_locale' => 'default_required_locale',
    'recipients_fields' => [
        'id',
        'external_user_id',
        'created_at',
        ...
    ]
];
```

# Usage

The package will automatically link your OneSignal recipients with your local database users' table using the "external_user_id" key ([see OneSignal API](https://documentation.onesignal.com/docs/internal-database-crm)). You can then simply select the users you want to send a localized notification to and the package will take care of the rest.

# Information

This package is under development.

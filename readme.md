# Hindsight - PHP SDK

## Installation

1. `composer require hindsight/php-sdk`

### On Laravel

1. `php artisan vendor:publish --tag=hindsight`
1. Insert your API key into the `config/hindsight.php` file and configure the remaining options to your liking
1. Add the `\Hindsight\Middleware\HindsightRequestLogger::class` middleware to the appropriate routes via `app/Http/Kernel.php` (to add it globally) or in the route files.

#### Laravel 5.6
On Laravel 5.6, register a new log channel with the `hindsight` driver:

```php
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            // Add hindsight to the stack:
            'channels' => ['single', 'hindsight'],
        ],

        // ...

        'hindsight' => [
            'driver' => 'hindsight',
        ],
    ],
```

#### Laravel < 5.6

If you are on Laravel 5.5, you don't need to do anything,we've automatically registered the
service provider and the hindsight logger.

If you are on Laravel 5.4 or earlier, add the `Hindsight\Providers\HindsightServiceProvider`
to your `config/app.php`.

### Without Laravel

If you are not using Laravel, you may manually configure your Monolog instance to start sending
logs to Hindsight. For convenience, we have a configuration class that you may use:

```php
use Hindsight\Hindsight;

Hindsight::setup($monologInstance, $yourApiKey);
```

If you wish to have a non-standard configuration, you may manually push the `Hindsight\Monolog\HindsightMonologHandler`
onto your Monolog instance.
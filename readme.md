# Hindsight - PHP SDK

## Installation

1. `composer require hindsight/php-sdk`
1. `php artisan vendor:publish --tag=hindsight`
1. Insert your API key into the `config/hindsight.php` file and configure XYZ fields
1. Add the `\Hindsight\Middleware\HindsightRequestLogger::class` middleware to the appropriate routes via `app/Http/Kernel.php` (to add it globally) or in the route files.

## Using Laravel < 5.6

If you are on an earlier Laravel version, you should (in addition to the above steps):

1. Add `Hindsight\Providers\HindsightPre56ServiceProvider` to the `providers` section of `config/app.php`
1. (Laravel 5.5 only) Add `Hindsight\Providers\HindsightServiceProvider` to the `dont-discover` section of your `composer.json` (see below for full example)

### Disabling auto-discovery (Laravel 5.5 only)

In Laravel 5.5, we need to disable auto discovery of the package, as that will discover the 5.6+ Service Provider. This is done by adding the following to your `composer.json`

```json
{
  ...

  "extra": {
    "laravel": {
      "dont-discover": [
        "Hindsight\\Providers\\HindsightServiceProvider"
      ]
    }
  }
}
```
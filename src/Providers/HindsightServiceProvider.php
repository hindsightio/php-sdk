<?php

namespace Hindsight\Providers;

use Monolog\Logger;
use Hindsight\Hindsight;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;

class HindsightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__.'/../../config/hindsight.php' => config_path('hindsight.php'),
        ], 'hindsight');

        // On Laravel 5.6, we will register the Hindsight log driver
        if ($this->app['log'] instanceof LogManager) {
            $this->app['log']->extend('hindsight', function ($app, array $config) {
                return Hindsight::setup(
                    new Logger(config('app.environment')),
                    config('hindsight.api_key'),
                    $config['level'] ?? Logger::DEBUG
                );
            });
        } else {
            Hindsight::setup(
                $this->app['log']->getMonolog(),
                config('hindsight.api_key', ''),
                config('hindsight.level', Logger::DEBUG)
            );
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

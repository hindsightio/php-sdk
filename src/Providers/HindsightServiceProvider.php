<?php

namespace Hindsight\Providers;

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

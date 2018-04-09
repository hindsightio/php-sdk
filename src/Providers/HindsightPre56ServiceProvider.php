<?php

namespace Hindsight\Providers;

use Hindsight\Hindsight;

class HindsightPre56ServiceProvider extends HindsightServiceProvider
{
    public function boot()
    {
        parent::boot();

        Hindsight::setup(\Log::getMonolog(), config('hindsight.api_key'), config('hindsight.level'));
    }
}

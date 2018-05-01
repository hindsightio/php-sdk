<?php

namespace Hindsight;

use Monolog\Logger;

class HindsightLogChannel
{
    public function __invoke(array $config)
    {
        return Hindsight::setup(
            new Logger(config('app.environment')),
            config('hindsight.api_key'),
            $config['level'] ?? Logger::DEBUG
        );
    }
}

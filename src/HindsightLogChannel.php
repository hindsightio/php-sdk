<?php

namespace Hindsight;

use Monolog\Logger;

/**
 * @deprecated The Hindsight log channel has been deprecated in favour of the Hindsight log driver as of version 0.3
 */
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

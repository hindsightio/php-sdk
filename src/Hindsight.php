<?php

namespace Hindsight;

use Monolog\Logger;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Hindsight\Monolog\HindsightMonologHandler;
use Decahedron\StickyLogging\StickyContextProcessor;

class Hindsight
{
    public static function setup(Logger $logger, $apiKey, $level = Logger::DEBUG)
    {
        return $logger->pushHandler(
            new WhatFailureGroupHandler([
                (new BufferHandler(
                    new HindsightMonologHandler($apiKey, $level)
                ))->pushProcessor(new StickyContextProcessor)
            ])
        );
    }
}

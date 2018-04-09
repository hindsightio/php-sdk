<?php

namespace Hindsight;

use Monolog\Logger;
use Hindsight\Monolog\HindsightMonologHandler;

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

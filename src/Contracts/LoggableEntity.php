<?php

namespace Hindsight\Contracts;

interface LoggableEntity
{
    /**
     * Convert the entity to a loggable array.
     *
     * @return array
     */
    public function toLoggableArray(): array;
}

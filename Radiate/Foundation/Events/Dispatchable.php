<?php

namespace Radiate\Foundation\Events;

use function Plugin\event;

trait Dispatchable
{
    /**
     * Dispatch an event.
     *
     * @param mixed ...$args
     * @return void
     */
    public static function dispatch(...$args): void
    {
        event(new static(...$args));
    }
}

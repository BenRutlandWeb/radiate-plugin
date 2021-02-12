<?php

namespace Radiate\Support\Facades;

class Event extends Facade
{
    /**
     * Get the name of the component
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'events';
    }
}

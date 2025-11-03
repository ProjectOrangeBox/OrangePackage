<?php

declare(strict_types=1);

namespace orange\framework\base\traits;

trait FactoryTraits
{
    /**
     * The method you use to generate
     * the Singleton's instance.
     *
     * This calls newInstance on the child class
     * and then stores it if you call getInstance again
     * that instance can be returned again
     */
    public static function getInstance(): mixed
    {
        $args = func_get_args();

        return static::newInstance(...$args);
    }
}

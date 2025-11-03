<?php

declare(strict_types=1);

namespace orange\framework\base\traits;

use orange\framework\exceptions\container\CannotCloneSingleton;
use orange\framework\exceptions\container\CannotUnserializeSingleton;

trait SingletonTraits
{
    /**
     * The actual singleton's instance almost always resides inside a static
     * field. In this case, the static field is an array, where each subclass of
     * the Singleton stores its own instance.
     */
    private static array $instances = [];

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
        $subclass = static::class;

        if (!isset(static::$instances[$subclass])) {
            // Note that here we use the "static" keyword instead of the actual
            // class name. In this context, the "static" keyword means "the name
            // of the current class". That detail is important because when the
            // method is called on the subclass, we want an instance of that
            // subclass to be created here.
            $args = func_get_args();

            static::$instances[$subclass] = static::newInstance(...$args);
        }

        return static::$instances[$subclass];
    }
}

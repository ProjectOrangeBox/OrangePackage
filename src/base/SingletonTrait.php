<?php

declare(strict_types=1);

namespace orange\framework\base;

use orange\framework\exceptions\container\CannotCloneSingleton;
use orange\framework\exceptions\container\CannotUnserializeSingleton;

trait SingletonTrait
{
    /**
     * The actual singleton's instance almost always resides inside a static
     * field. In this case, the static field is an array, where each subclass of
     * the Singleton stores its own instance.
     */
    private static array $instances = [];

    // the default instance config
    protected array $config = [];

    /**
     * singletons should not have public constructors
     *
     * @return void
     */
    protected function __construct() {
        // placeholder
    }

    /**
     * The method you use to get the Singleton's instance.
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

    /**
     * Allow the creation of a new instance for testing etc...
     */
    public static function newInstance(): mixed
    {
        $args = func_get_args();

        return new static(...$args);
    }

    public function __clone()
    {
        throw new CannotCloneSingleton();
    }

    public function __wakeup()
    {
        throw new CannotUnserializeSingleton();
    }
}

<?php

declare(strict_types=1);

namespace orange\framework\base;

use orange\framework\base\SingletonTrait;

/**
 * Extend and replace some of Factories methods
 */
class Singleton extends Factory
{
    use SingletonTrait;
}

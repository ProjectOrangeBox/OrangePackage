<?php

declare(strict_types=1);

namespace orange\framework\base;

use orange\framework\base\traits\BaseTraits;
use orange\framework\base\traits\SingletonTraits;

class Singleton extends Factory
{
    use BaseTraits;
    use SingletonTraits;
}

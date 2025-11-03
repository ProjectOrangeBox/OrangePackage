<?php

declare(strict_types=1);

namespace orange\framework\base;

use orange\framework\base\ArrayObject;
use orange\framework\base\SingletonTrait;

class SingletonArrayObject extends ArrayObject
{
    use SingletonTrait;
}

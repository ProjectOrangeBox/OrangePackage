<?php

declare(strict_types=1);

namespace orange\framework\base;

use orange\framework\base\ArrayObject;
use orange\framework\base\traits\BaseTraits;
use orange\framework\base\traits\SingletonTraits;

class SingletonArrayObject extends ArrayObject
{
    use BaseTraits;
    use SingletonTraits;
}

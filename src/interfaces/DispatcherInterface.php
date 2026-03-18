<?php

declare(strict_types=1);

namespace orange\framework\interfaces;

use orange\framework\property\RouterCallback;

interface DispatcherInterface
{
    public const CONTROLLER = 0;
    public const METHOD = 1;

    public function call(RouterCallback $routerCallback): string;
}

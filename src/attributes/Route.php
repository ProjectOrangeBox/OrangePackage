<?php

declare(strict_types=1);

namespace orange\framework\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route {
    public function __construct(public string $httpMethods, public string $url, public string $name = '')
    {
    }
}
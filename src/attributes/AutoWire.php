<?php

declare(strict_types=1);

namespace orange\framework\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AutoWire {
    public function __construct(public string $service)
    {
    }
}
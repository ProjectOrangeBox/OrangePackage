<?php

declare(strict_types=1);

namespace orange\framework\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AttachService {
    public function __construct(public string $attachService)
    {
    }
}
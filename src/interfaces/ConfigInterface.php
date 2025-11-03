<?php

declare(strict_types=1);

namespace orange\framework\interfaces;

interface ConfigInterface
{
    public function __get(string $filename): mixed;
    public function get(string $filenameKey, mixed $defaultValue = null): mixed;
}

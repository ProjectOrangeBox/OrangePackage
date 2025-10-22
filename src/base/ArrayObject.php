<?php

declare(strict_types=1);

namespace orange\framework\base;

use ArrayObject as PHPArrayObject;
use orange\framework\exceptions\dispatcher\MethodNotFound;

class ArrayObject extends PHPArrayObject
{
    public function __construct(array $input = [])
    {
        parent::__construct($input, PHPArrayObject::ARRAY_AS_PROPS);
    }

    public function __call($func, $argv)
    {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_') {
            throw new MethodNotFound(__CLASS__ . '->' . $func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }

    public function has(string $name): bool
    {
        return isset($this[$name]);
    }

    public function get(string $name, mixed $default): mixed
    {
        return isset($this[$name]) ? $this[$name] : $default;
    }
}

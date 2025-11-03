<?php

declare(strict_types=1);

namespace orange\framework\base;

use ArrayObject as PHPArrayObject;
use orange\framework\exceptions\MagicMethodNotFound;

class ArrayObject extends PHPArrayObject
{
    public function __construct(array $input = [])
    {
        parent::__construct($input, PHPArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * let "some" of the array_ functions work
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws MagicMethodNotFound
     */
    public function __call(string $name, array $arguments)
    {
        if (!is_callable($name) || substr($name, 0, 6) !== 'array_') {
            throw new MagicMethodNotFound(__CLASS__ . '->' . $name);
        }

        return call_user_func_array($name, array_merge([$this->getArrayCopy()], $arguments));
    }

    public function has(string $name): bool
    {
        return isset($this[$name]);
    }

    public function get(string $name, mixed $default): mixed
    {
        return isset($this[$name]) ? $this[$name] : $default;
    }
        /**
     * build a recusrive array of ArrayObject's
     *
     * @param array $data
     * @return array
     */
    protected function buildArrayObjects(array $data)
    {
        $array = [];

        foreach ($data as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
        /**
     * Allow ArrayObject "merging"
     *
     * @param array $array
     * @param bool $recursive
     * @param bool $replace
     * @return static
     */
    public function merge(array $array, bool $recursive = true, bool $replace = true): static
    {
        // convert ArrayObject into an array
        $currentArray = (array)$this;

        // more than likely you want to replace what is already in data not merge with it
        if ($replace) {
            $data = ($recursive) ? array_replace_recursive($currentArray, $array) : array_replace($currentArray, $array);
        } else {
            $data = ($recursive) ? array_merge_recursive($currentArray, $array) : array_merge($currentArray, $array);
        }

        // swap
        $this->exchangeArray($this->buildArrayObjects($data));

        return $this;
    }
}

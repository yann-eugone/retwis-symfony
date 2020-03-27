<?php

namespace App\Redis;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use function get_class;
use function strtolower;

final class ObjectKeys
{
    public function object(object $object): string
    {
        return $this->class(get_class($object));
    }

    public function class(string $class): string
    {
        try {
            return strtolower(
                (new ReflectionClass($class))->getShortName()
            );
        } catch (ReflectionException $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }
    }
}

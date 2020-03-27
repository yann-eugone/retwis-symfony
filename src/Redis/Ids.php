<?php

namespace App\Redis;

use Predis\ClientInterface;
use function strlen;

final class Ids
{
    private ObjectKeys $key;

    private ClientInterface $redis;

    public function __construct(ObjectKeys $key, ClientInterface $redis)
    {
        $this->key = $key;
        $this->redis = $redis;
    }

    public function id(string $class): int
    {
        $field = $this->key->class($class);
        $increment = 1;

        if (strlen($this->redis->hget('ids', $field)) === 0) {
            $increment = 2;
        }

        return $this->redis->hincrby('ids', $field, $increment);
    }
}

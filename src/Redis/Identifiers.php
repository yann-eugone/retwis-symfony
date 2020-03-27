<?php

namespace App\Redis;

use Predis\ClientInterface;
use function strlen;

final class Identifiers
{
    private ObjectKeys $key;

    private ClientInterface $redis;

    public function __construct(ObjectKeys $key, ClientInterface $redis)
    {
        $this->key = $key;
        $this->redis = $redis;
    }

    public function set(string $class, string $id, string $identifier): void
    {
        $key = $this->key($class);

        $this->redis->hset($key, $identifier, $id);
    }

    public function has(string $class, string $identifier): bool
    {
        $key = $this->key($class);

        return strlen($this->redis->hget($key, $identifier)) > 0;
    }

    public function id(string $class, string $identifier): string
    {
        $key = $this->key($class);

        $id = $this->redis->hget($key, $identifier);
        if (!$id) {
            throw new NotFoundException(
                sprintf('There is no identifier %s.', $identifier)
            );
        }

        return $id;
    }

    private function key(string $class): string
    {
        return 'identifiers:' . $this->key->class($class);
    }
}

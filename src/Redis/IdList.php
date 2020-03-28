<?php

namespace App\Redis;

use Predis\ClientInterface;

final class IdList
{
    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function push(string $list, string $id, int $length): void
    {
        $this->redis->lpush($list, [$id]);
        $this->redis->ltrim($list, 0, $length - 1);
    }

    /**
     * @param string $list
     *
     * @return string[]
     */
    public function ids(string $list): array
    {
        return $this->redis->lrange($list, 0, -1);
    }
}

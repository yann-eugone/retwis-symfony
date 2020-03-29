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

    public function push(string $list, string $id, int $length, int $score): void
    {
        $this->redis->zadd($list, [$id => $score]);
        $this->redis->zremrangebyrank($list, 0, ($length + 1) * -1);
    }

    /**
     * @param string $list
     *
     * @return string[]
     */
    public function ids(string $list): array
    {
        return $this->redis->zrange($list, 0, -1);
    }
}

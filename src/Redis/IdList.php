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

    public function push(string $list, string $id, int $score): void
    {
        $this->redis->zadd($list, [$id => $score]);
    }

    /**
     * @param string $list
     * @param int    $start
     * @param int    $stop
     *
     * @return string[]
     */
    public function ids(string $list, int $start = 0, int $stop = -1): array
    {
        return $this->redis->zrange($list, $start, $stop);
    }

    public function count(string $list): int
    {
        return $this->redis->zcard($list);
    }
}

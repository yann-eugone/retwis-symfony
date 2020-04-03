<?php

namespace App\Timeline;

use Predis\ClientInterface;
use function ints;

final class Timelines
{
    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function add(string $timeline, int $userId, int $postId, int $postTime): void
    {
        $key = $this->key($timeline, $userId);

        $this->redis->zadd($key, [$postId => $postTime]);
    }

    public function addAll(string $timeline, int $userId, array $posts): void
    {
        $key = $this->key($timeline, $userId);

        $this->redis->zadd($key, $posts);
    }

    public function remove(string $timeline, int $userId, int $postId): void
    {
        $key = $this->key($timeline, $userId);

        $this->redis->zrem($key, $postId);
    }

    public function removeAll(string $timeline, int $userId, array $posts): void
    {
        $key = $this->key($timeline, $userId);

        $this->redis->zrem($key, ...$posts);
    }

    public function count(string $timeline, int $authorId): int
    {
        $key = $this->key($timeline, $authorId);

        return $this->redis->zcard($key);
    }

    public function ids(string $timeline, int $authorId, int $start = 0, int $stop = 9): array
    {
        $key = $this->key($timeline, $authorId);

        return ints($this->redis->zrevrange($key, $start, $stop));
    }

    public function map(string $timeline, int $authorId): array
    {
        $key = $this->key($timeline, $authorId);

        return $this->redis->zrevrange($key, 0, -1, ['withscores' => true]);
    }

    private function key(string $type, int $authorId): string
    {
        return 'timeline:' . $type . ':' . $authorId;
    }
}

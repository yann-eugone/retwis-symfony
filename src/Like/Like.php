<?php

namespace App\Like;

use Predis\ClientInterface;

final class Like
{
    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public function count(int $postId): int
    {
        $key = $this->key($postId);

        return $this->redis->zcard($key);
    }

    public function like(int $postId, int $userId): void
    {
        $key = $this->key($postId);

        $this->redis->zadd($key, [$userId => time()]);
    }

    public function unlike(int $postId, int $userId): void
    {
        $key = $this->key($postId);

        $this->redis->zrem($key, $userId);
    }

    public function isLiking(int $postId, int $userId): bool
    {
        $key = $this->key($postId);

        return $this->redis->zrank($key, $userId) !== null;
    }

    private function key(int $postId): string
    {
        return 'post:likes:' . $postId;
    }
}

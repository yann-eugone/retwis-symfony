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

    public function like(int $postId, int $userId, int $time = null): void
    {
        $time ??= time();
        $postKey = $this->postKey($postId);
        $userKey = $this->userKey($userId);

        $this->redis->zadd($postKey, [$userId => $time]);
        $this->redis->zadd($userKey, [$postId => $time]);
    }

    public function unlike(int $postId, int $userId): void
    {
        $postKey = $this->postKey($postId);
        $userKey = $this->userKey($userId);

        $this->redis->zrem($postKey, $userId);
        $this->redis->zrem($userKey, $postId);
    }

    public function postCount(int $userId): int
    {
        $key = $this->postKey($userId);

        return $this->redis->zcard($key);
    }

    /**
     * @param int $userId
     * @param int $start
     * @param int $length
     *
     * @return int[]
     */
    public function listPostIds(int $userId, int $start = 0, int $length = 10): array
    {
        $key = $this->userKey($userId);

        return array_map('intval', $this->redis->zrange($key, $start, $start + $length - 1));
    }

    public function userCount(int $postId): int
    {
        $key = $this->postKey($postId);

        return $this->redis->zcard($key);
    }

    /**
     * @param int $postId
     * @param int $start
     * @param int $length
     *
     * @return int[]
     */
    public function listUserIds(int $postId, int $start = 0, int $length = 10): array
    {
        $key = $this->postKey($postId);

        return array_map('intval', $this->redis->zrange($key, $start, $start + $length - 1));
    }

    public function isLiking(int $postId, int $userId): bool
    {
        $key = $this->postKey($postId);

        return $this->redis->zrank($key, $userId) !== null;
    }

    private function postKey(int $postId): string
    {
        return 'post:likes:' . $postId;
    }

    private function userKey(int $userId): string
    {
        return 'user:likes:' . $userId;
    }
}

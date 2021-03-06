<?php

namespace App\Follow;

use App\Follow\Event\FollowEvent;
use App\Follow\Event\UnfollowEvent;
use Predis\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use function ints;

final class Follow
{
    private ClientInterface $redis;

    private EventDispatcherInterface $events;

    public function __construct(ClientInterface $redis, EventDispatcherInterface $events)
    {
        $this->redis = $redis;
        $this->events = $events;
    }

    public function following(int $userId, int $start = 0, int $stop = -1): array
    {
        $key = $this->followingKey($userId);

        return ints($this->redis->zrevrange($key, $start, $stop));
    }

    public function followingCount(int $userId): int
    {
        $key = $this->followingKey($userId);

        return $this->redis->zcard($key);
    }

    public function followers(int $userId, int $start = 0, int $stop = -1): array
    {
        $key = $this->followersKey($userId);

        return ints($this->redis->zrevrange($key, $start, $stop));
    }

    public function followersCount(int $userId): int
    {
        $key = $this->followersKey($userId);

        return $this->redis->zcard($key);
    }

    public function follow(int $followerId, int $followingId, int $time = null): void
    {
        $time ??= time();

        // add $followerId to $followingId followers
        $followersKey = $this->followersKey($followingId);
        $this->redis->zadd($followersKey, [$followerId => $time]);

        // add $followingId to $followerId following
        $followingKey = $this->followingKey($followerId);
        $this->redis->zadd($followingKey, [$followingId => $time]);

        $this->events->dispatch(new FollowEvent($followerId, $followingId, $time));
    }

    public function unfollow(int $followerId, int $followingId, int $time = null): void
    {
        $time ??= time();

        // remove $followerId from $followingId followers
        $followersKey = $this->followersKey($followingId);
        $this->redis->zrem($followersKey, $followerId);

        // remove $followingId from $followerId following
        $followingKey = $this->followingKey($followerId);
        $this->redis->zrem($followingKey, $followingId);

        $this->events->dispatch(new UnfollowEvent($followerId, $followingId, $time));
    }

    public function isFollowing(int $followerId, int $followingId): bool
    {
        $followingKey = $this->followingKey($followerId);

        return $this->redis->zrank($followingKey, $followingId) !== null;
    }

    private function followersKey(int $userId): string
    {
        return 'followers:' . $userId;
    }

    private function followingKey(int $userId): string
    {
        return 'following:' . $userId;
    }
}

<?php

namespace App\Follow;

use App\Follow\Event\FollowEvent;
use App\Follow\Event\UnfollowEvent;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MostFollowedUsers implements EventSubscriberInterface
{
    private const USER_POPULARITY_REDIS_KEY = 'user:popularity';

    private ClientInterface $redis;

    private Follow $follow;

    public function __construct(ClientInterface $redis, Follow $follow)
    {
        $this->redis = $redis;
        $this->follow = $follow;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FollowEvent::class => 'onFollow',
            UnfollowEvent::class => 'onUnfollow',
        ];
    }

    public function onFollow(FollowEvent $event): void
    {
        $this->set($event->getFollowingId());
    }

    public function onUnfollow(UnfollowEvent $event): void
    {
        $this->set($event->getFollowingId());
    }

    private function set(int $userId): void
    {
        $this->redis->zadd(self::USER_POPULARITY_REDIS_KEY, [$userId => $this->follow->followersCount($userId)]);
    }

    public function count(): int
    {
        return $this->redis->zcard(self::USER_POPULARITY_REDIS_KEY);
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return array<int,int>
     */
    public function list(int $start = 0, int $length = 10): array
    {
        return $this->redis->zrevrange(
            self::USER_POPULARITY_REDIS_KEY,
            $start,
            $start + $length - 1,
            ['withscores' => true]
        );
    }
}

<?php

namespace App\User;

use App\User\Event\UserRegisteredEvent;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyRegisteredUsers implements EventSubscriberInterface
{
    private const REDIS_KEY = 'users:recently-registered';

    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $this->redis->zadd(self::REDIS_KEY, [$event->getId() => $event->getRegistered()]);
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return array<int,int>
     */
    public function list(int $start = 0, int $length = 10): array
    {
        return $this->redis->zrevrange(self::REDIS_KEY, $start, $start + $length - 1, ['withscores' => true]);
    }
}

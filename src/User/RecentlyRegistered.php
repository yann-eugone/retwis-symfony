<?php

namespace App\User;

use App\User\Event\UserRegisteredEvent;
use Generator;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function ints;

final class RecentlyRegistered implements EventSubscriberInterface
{
    private const REDIS_KEY = 'users:recently-registered';

    private ClientInterface $redis;

    private UserStorage $users;

    public function __construct(ClientInterface $redis, UserStorage $users)
    {
        $this->redis = $redis;
        $this->users = $users;
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

    public function count(): int
    {
        return $this->redis->zcard(self::REDIS_KEY);
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return Generator|User[]
     */
    public function list(int $start = 0, int $length = 10): Generator
    {
        $ids = $this->redis->zrevrange(self::REDIS_KEY, $start, $start + $length - 1);

        yield from $this->users->list(ints($ids));
    }
}

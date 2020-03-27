<?php

namespace App\User;

use App\User\Event\UserRegistered;
use Generator;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyRegisteredUsers implements EventSubscriberInterface
{
    private const REDIS_KEY = 'users:recently-registered';
    private const LENGTH = 10;

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
            UserRegistered::class => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegistered $event): void
    {
        $this->redis->lpush(self::REDIS_KEY, [$event->getId()]);
        $this->redis->ltrim(self::REDIS_KEY, 0, self::LENGTH - 1);
    }

    /**
     * @return Generator|User[]
     */
    public function get(): Generator
    {
        foreach ($this->redis->lrange(self::REDIS_KEY, 0, -1) as $id) {
            yield $this->users->get((int)$id);
        }
    }
}

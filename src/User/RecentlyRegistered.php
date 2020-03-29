<?php

namespace App\User;

use App\Redis\IdList;
use App\User\Event\UserRegistered;
use Generator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyRegistered implements EventSubscriberInterface
{
    private const REDIS_KEY = 'users:recently-registered';
    private const LENGTH = 10;

    private IdList $list;

    private UserStorage $users;

    public function __construct(IdList $list, UserStorage $users)
    {
        $this->list = $list;
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
        $this->list->push(self::REDIS_KEY, (string)$event->getId(), self::LENGTH, $event->getRegistered());
    }

    /**
     * @return Generator|User[]
     */
    public function list(): Generator
    {
        yield from $this->users->list(
            array_map('intval', $this->list->ids(self::REDIS_KEY))
        );
    }
}

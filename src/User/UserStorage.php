<?php

namespace App\User;

use App\Redis\Ids;
use App\Redis\NotFoundException;
use App\Redis\ObjectDictionary;
use App\User\Event\UserRegistered;
use Generator;
use Predis\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

final class UserStorage
{
    private EncoderFactoryInterface $password;

    private Ids $ids;

    private ClientInterface $redis;

    private ObjectDictionary $objectDictionary;

    private EventDispatcherInterface $events;

    public function __construct(
        EncoderFactoryInterface $password,
        Ids $ids,
        ClientInterface $redis,
        ObjectDictionary $objectDictionary,
        EventDispatcherInterface $events
    ) {
        $this->password = $password;
        $this->ids = $ids;
        $this->redis = $redis;
        $this->objectDictionary = $objectDictionary;
        $this->events = $events;
    }

    public function register(string $name, string $username, string $plainPassword, int $time = null): User
    {
        $time ??= time();

        $id = $this->ids->id(User::class);
        $password = $this->password->getEncoder(User::class)
            ->encodePassword($plainPassword, null);

        $user = new User($id, $name, $username, $password, $time);

        $key = $this->key($id);
        $dictionary = $this->objectDictionary->dictionary($user);

        $this->redis->hmset($key, $dictionary);
        $this->redis->hset('users:identifiers', $username, $id);

        $this->events->dispatch(UserRegistered::fromUser($user));

        return $user;
    }

    public function update(User $user): void
    {
        $key = $this->key($user->getId());
        $dictionary = $this->objectDictionary->dictionary($user);

        $this->redis->hmset($key, $dictionary);
    }

    public function get(int $id): User
    {
        $key = $this->key($id);
        $dictionary = $this->redis->hgetall($key);

        return $this->objectDictionary->object(User::class, $dictionary);
    }

    public function id(string $username): int
    {
        $id = $this->redis->hget('users:identifiers', $username);
        if (strlen($id) === 0) {
            throw new NotFoundException(
                sprintf('There is no user with username %s.', $username)
            );
        }

        return (int)$id;
    }

    /**
     * @param iterable|int[] $ids
     *
     * @return Generator|User[]
     */
    public function list(iterable $ids): Generator
    {
        foreach ($ids as $id) {
            yield $this->get($id);
        }
    }

    private function key(int $id): string
    {
        return 'user:' . $id;
    }
}

<?php

namespace App\User;

use App\Redis\NotFoundException;
use App\Redis\ObjectDictionary;
use App\User\Event\UserRegisteredEvent;
use Generator;
use Predis\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use function dump;

final class UserStorage
{
    private const REDIS_ID_KEY = 'users:next_id';

    private EncoderFactoryInterface $password;

    private ObjectDictionary $objectDictionary;

    private ClientInterface $redis;

    private EventDispatcherInterface $events;

    public function __construct(
        EncoderFactoryInterface $password,
        ObjectDictionary $objectDictionary,
        ClientInterface $redis,
        EventDispatcherInterface $events
    ) {
        $this->password = $password;
        $this->objectDictionary = $objectDictionary;
        $this->redis = $redis;
        $this->events = $events;
    }

    public function register(string $name, string $username, string $plainPassword, int $time = null): User
    {
        $time ??= time();

        $id = $this->redis->incr(self::REDIS_ID_KEY);
        $password = $this->password->getEncoder(User::class)
            ->encodePassword($plainPassword, null);

        $user = new User($id, $name, $username, $password, $time);

        $key = $this->key($id);
        $dictionary = $this->objectDictionary->dictionary($user);

        $this->redis->hmset($key, $dictionary);
        $this->redis->hset('users:identifiers', $username, $id);

        $this->events->dispatch(UserRegisteredEvent::fromUser($user));

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
        foreach ($ids as $index => $id) {
            dump($id);
            yield $index => $this->get($id);
        }
    }

    private function key(int $id): string
    {
        return 'user:' . $id;
    }
}

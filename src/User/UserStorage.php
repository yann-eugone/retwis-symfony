<?php

namespace App\User;

use App\Redis\Ids;
use App\Redis\NotFoundException;
use App\Redis\Objects;
use App\User\Event\UserRegistered;
use Generator;
use Predis\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

final class UserStorage
{
    private EncoderFactoryInterface $password;

    private Ids $ids;

    private Objects $objects;

    private ClientInterface $redis;

    private EventDispatcherInterface $events;

    public function __construct(
        EncoderFactoryInterface $password,
        Ids $ids,
        Objects $objects,
        ClientInterface $redis,
        EventDispatcherInterface $events
    ) {
        $this->password = $password;
        $this->ids = $ids;
        $this->objects = $objects;
        $this->redis = $redis;
        $this->events = $events;
    }

    public function register(string $name, string $username, string $plainPassword, int $time = null): User
    {
        $time ??= time();

        $id = $this->ids->id(User::class);
        $password = $this->password->getEncoder(User::class)
            ->encodePassword($plainPassword, null);

        $user = new User($id, $name, $username, $password, $time);

        $this->objects->add((string)$id, $user);
        $this->redis->hset('users:identifiers', $username, $id);
        $this->events->dispatch(UserRegistered::fromUser($user));

        return $user;
    }

    public function update(User $user): void
    {
        $this->objects->update((string)$user->getId(), $user);
    }

    public function get(int $id): User
    {
        return $this->objects->get(User::class, (string)$id);
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
}

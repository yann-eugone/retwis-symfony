<?php

namespace App\User;

use App\Redis\Identifiers;
use App\Redis\Ids;
use App\Redis\Objects;
use App\User\Event\UserRegistered;
use Generator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

final class UserStorage
{
    private EncoderFactoryInterface $password;

    private Ids $ids;

    private Objects $objects;

    private Identifiers $identifiers;

    private EventDispatcherInterface $events;

    public function __construct(
        EncoderFactoryInterface $password,
        Ids $ids,
        Objects $objects,
        Identifiers $identifiers,
        EventDispatcherInterface $events
    ) {
        $this->password = $password;
        $this->ids = $ids;
        $this->objects = $objects;
        $this->identifiers = $identifiers;
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
        $this->identifiers->set(User::class, $id, $username);
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
        return (int)$this->identifiers->id(User::class, $username);
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

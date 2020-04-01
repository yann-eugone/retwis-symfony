<?php

namespace App\User\Event;

use App\User\User;

final class UserRegisteredEvent
{
    private int $id;

    private string $username;

    private int $registered;

    public function __construct(int $id, string $username, int $registered)
    {
        $this->id = $id;
        $this->username = $username;
        $this->registered = $registered;
    }

    public static function fromUser(User $user)
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getRegistered(),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRegistered(): int
    {
        return $this->registered;
    }
}

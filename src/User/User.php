<?php

namespace App\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class User implements UserInterface
{
    /**
     * @Groups({"redis"})
     */
    private int $id;

    /**
     * @Groups({"redis"})
     */
    private string $username;

    /**
     * @Groups({"redis"})
     */
    private string $password;

    /**
     * @Groups({"redis"})
     */
    private int $registered;

    public function __construct(int $id, string $username, string $password, int $registered)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->registered = $registered;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRegistered(): int
    {
        return $this->registered;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}

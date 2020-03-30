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
    private string $name;

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

    /**
     * @Groups({"redis"})
     */
    private ?string $bio = null;

    /**
     * @Groups({"redis"})
     */
    private ?string $location = null;

    /**
     * @Groups({"redis"})
     */
    private ?string $website = null;

    public function __construct(
        int $id,
        string $name,
        string $username,
        string $password,
        int $registered,
        string $bio = null,
        string $location = null,
        string $website = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->password = $password;
        $this->registered = $registered;
        $this->bio = $bio;
        $this->location = $location;
        $this->website = $website;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function fillProfile(string $name, ?string $bio, ?string $location, ?string $website): void
    {
        $this->name = $name;
        $this->bio = $bio;
        $this->location = $location;
        $this->website = $website;
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

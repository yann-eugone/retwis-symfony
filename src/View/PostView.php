<?php

namespace App\View;

use App\User\User;
use DateTimeImmutable;

final class PostView
{
    private int $id;

    private string $hash;

    private User $author;

    private string $message;

    private DateTimeImmutable $published;

    private int $likes;

    public function __construct(int $id, string $hash, User $author, string $message, DateTimeImmutable $published, int $likes)
    {
        $this->id = $id;
        $this->hash = $hash;
        $this->author = $author;
        $this->message = $message;
        $this->published = $published;
        $this->likes = $likes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPublished(): DateTimeImmutable
    {
        return $this->published;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }
}

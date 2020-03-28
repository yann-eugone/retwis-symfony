<?php

namespace App\View;

use App\Post\Post;
use App\User\User;
use App\User\UserStorage;
use DateTimeImmutable;
use Hashids\HashidsInterface;

final class PostView
{
    private int $id;

    private string $hash;

    private User $author;

    private string $message;

    private DateTimeImmutable $published;

    private function __construct(int $id, string $hash, User $author, string $message, DateTimeImmutable $published)
    {
        $this->id = $id;
        $this->hash = $hash;
        $this->author = $author;
        $this->message = $message;
        $this->published = $published;
    }

    public static function new(Post $post, UserStorage $users, HashidsInterface $hashids): self
    {
        return new self(
            $post->getId(),
            $hashids->encode($post->getId()),
            $users->get($post->getAuthor()),
            $post->getMessage(),
            (new DateTimeImmutable())->setTimestamp($post->getTime())
        );
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
}

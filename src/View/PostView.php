<?php

namespace App\View;

use App\Like\Like;
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

    private int $likes;

    private function __construct(int $id, string $hash, User $author, string $message, DateTimeImmutable $published, int $likes)
    {
        $this->id = $id;
        $this->hash = $hash;
        $this->author = $author;
        $this->message = $message;
        $this->published = $published;
        $this->likes = $likes;
    }

    public static function new(Post $post, UserStorage $users, HashidsInterface $hashids, Like $like): self
    {
        return new self(
            $post->getId(),
            $hashids->encode($post->getId()),
            $users->get($post->getAuthor()),
            $post->getMessage(),
            (new DateTimeImmutable())->setTimestamp($post->getPublished()),
            $like->postCount($post->getId())
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

    public function getLikes(): int
    {
        return $this->likes;
    }
}

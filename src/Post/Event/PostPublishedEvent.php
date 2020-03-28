<?php

namespace App\Post\Event;

use App\Post\Post;

final class PostPublishedEvent
{
    private int $id;

    private int $author;

    private string $message;

    private int $time;

    public function __construct(int $id, int $author, string $message, int $time)
    {
        $this->id = $id;
        $this->author = $author;
        $this->message = $message;
        $this->time = $time;
    }

    public static function fromPost(Post $post): self
    {
        return new self(
            $post->getId(),
            $post->getAuthor(),
            $post->getMessage(),
            $post->getTime()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthor(): int
    {
        return $this->author;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}

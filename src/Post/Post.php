<?php

namespace App\Post;

use Symfony\Component\Serializer\Annotation\Groups;

final class Post
{
    /**
     * @Groups({"redis"})
     */
    private int $id;

    /**
     * @Groups({"redis"})
     */
    private int $author;

    /**
     * @Groups({"redis"})
     */
    private string $message;

    /**
     * @Groups({"redis"})
     */
    private int $published;

    public function __construct(int $id, int $author, string $message, int $published)
    {
        $this->id = $id;
        $this->author = $author;
        $this->message = $message;
        $this->published = $published;
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

    public function getPublished(): int
    {
        return $this->published;
    }
}

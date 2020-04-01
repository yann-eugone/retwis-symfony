<?php

namespace App\Like\Event;

final class LikeEvent
{
    private int $postId;

    private int $userId;

    private int $time;

    public function __construct(int $postId, int $userId, int $time)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->time = $time;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}

<?php

namespace App\Follow\Event;

final class FollowEvent
{
    private int $followerId;

    private int $followingId;

    private int $time;

    public function __construct(int $followerId, int $followingId, int $time)
    {
        $this->followerId = $followerId;
        $this->followingId = $followingId;
        $this->time = $time;
    }

    public function getFollowerId(): int
    {
        return $this->followerId;
    }

    public function getFollowingId(): int
    {
        return $this->followingId;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}

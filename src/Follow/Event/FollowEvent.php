<?php

namespace App\Follow\Event;

final class FollowEvent
{
    /**
     * @var int
     */
    private int $followerId;

    /**
     * @var int
     */
    private int $followingId;

    public function __construct(int $followerId, int $followingId)
    {
        $this->followerId = $followerId;
        $this->followingId = $followingId;
    }

    public function getFollowerId(): int
    {
        return $this->followerId;
    }

    public function getFollowingId(): int
    {
        return $this->followingId;
    }
}

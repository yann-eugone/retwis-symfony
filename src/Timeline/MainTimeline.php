<?php

namespace App\Timeline;

use App\Follow\Event\FollowEvent;
use App\Follow\Event\UnfollowEvent;
use App\Follow\Follow;
use App\Post\Event\PostPublishedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MainTimeline implements EventSubscriberInterface
{
    private const TIMELINE = 'main';

    private Timelines $timelines;

    private Follow $follow;

    private PersonalTimeline $personalTimeline;

    public function __construct(Timelines $timelines, Follow $follow, PersonalTimeline $personalTimeline)
    {
        $this->timelines = $timelines;
        $this->follow = $follow;
        $this->personalTimeline = $personalTimeline;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostPublishedEvent::class => 'onPostPublished',
            FollowEvent::class => 'onFollow',
            UnfollowEvent::class => 'onUnfollow',
        ];
    }

    public function onPostPublished(PostPublishedEvent $event): void
    {
        foreach ($this->follow->followers($event->getAuthor()) as $followerId) {
            $this->timelines->add(self::TIMELINE, $followerId, $event->getId(), $event->getPublished());
        }
    }

    public function onFollow(FollowEvent $event): void
    {
        $followingPosts = $this->personalTimeline->map($event->getFollowingId());
        if (count($followingPosts) === 0) {
            return;
        }

        $this->timelines->addAll(self::TIMELINE, $event->getFollowerId(), $followingPosts);
    }

    public function onUnfollow(UnfollowEvent $event): void
    {
        $followingPosts = $this->personalTimeline->ids($event->getFollowingId(), 0, -1);
        if (count($followingPosts) === 0) {
            return;
        }

        $this->timelines->removeAll(self::TIMELINE, $event->getFollowerId(), $followingPosts);
    }

    public function count(int $authorId): int
    {
        return $this->timelines->count(self::TIMELINE, $authorId);
    }

    public function ids(int $authorId, int $start = 0, int $length = 10): array
    {
        return $this->timelines->ids(self::TIMELINE, $authorId, $start, $start + $length - 1);
    }
}

<?php

namespace App\Timeline;

use App\Post\Event\PostPublishedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PersonalTimeline implements EventSubscriberInterface
{
    private const TIMELINE = 'personal';

    private Timelines $timelines;

    public function __construct(Timelines $timelines)
    {
        $this->timelines = $timelines;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostPublishedEvent::class => 'onPostPublished',
        ];
    }

    public function onPostPublished(PostPublishedEvent $event): void
    {
        $this->timelines->add(self::TIMELINE, $event->getAuthor(), $event->getId(), $event->getPublished());
    }

    public function count(int $authorId): int
    {
        return $this->timelines->count(self::TIMELINE, $authorId);
    }

    public function ids(int $authorId, int $start = 0, int $length = 10): array
    {
        return $this->timelines->ids(self::TIMELINE, $authorId, $start, $start + $length - 1);
    }

    public function map(int $authorId): array
    {
        return $this->timelines->map(self::TIMELINE, $authorId);
    }
}

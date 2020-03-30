<?php

namespace App\Post;

use App\Post\Event\PostPublishedEvent;
use App\Redis\IdList;
use Generator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyPublished implements EventSubscriberInterface
{
    private const REDIS_KEY = 'posts:recently-published';

    private IdList $list;

    private PostStorage $posts;

    public function __construct(IdList $list, PostStorage $posts)
    {
        $this->list = $list;
        $this->posts = $posts;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostPublishedEvent::class => 'onPostPublished',
        ];
    }

    public function onPostPublished(PostPublishedEvent $event): void
    {
        $this->list->push(self::REDIS_KEY, (string)$event->getId(), $event->getPublished());
    }

    public function count(): int
    {
        return $this->list->count(self::REDIS_KEY);
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return Generator|Post[]
     */
    public function list(int $start = 0, int $length = 10): Generator
    {
        yield from $this->posts->list(
            array_map('intval', $this->list->ids(self::REDIS_KEY, $start, $start + $length - 1))
        );
    }
}

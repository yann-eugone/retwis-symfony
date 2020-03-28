<?php

namespace App\Post;

use App\Post\Event\PostPublishedEvent;
use App\Redis\IdList;
use Generator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyPublished implements EventSubscriberInterface
{
    private const REDIS_KEY = 'posts:recently-published';
    private const LENGTH = 100;

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
        $this->list->push(self::REDIS_KEY, (string)$event->getId(), self::LENGTH);
    }

    /**
     * @return Generator|Post[]
     */
    public function list(): Generator
    {
        yield from $this->posts->list(
            array_map('intval', $this->list->ids(self::REDIS_KEY))
        );
    }
}

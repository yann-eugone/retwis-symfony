<?php

namespace App\Post;

use App\Post\Event\PostPublishedEvent;
use Generator;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecentlyPublished implements EventSubscriberInterface
{
    private const REDIS_KEY = 'posts:recently-published';

    private ClientInterface $redis;

    private PostStorage $posts;

    public function __construct(ClientInterface $redis, PostStorage $posts)
    {
        $this->redis = $redis;
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
        $this->redis->zadd(self::REDIS_KEY, [$event->getId() => $event->getPublished()]);
    }

    public function count(): int
    {
        return $this->redis->zcard(self::REDIS_KEY);
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return Generator|Post[]
     */
    public function list(int $start = 0, int $length = 10): Generator
    {
        $ids = $this->redis->zrange(self::REDIS_KEY, $start, $start + $length - 1);

        yield from $this->posts->list(ints($ids));
    }
}

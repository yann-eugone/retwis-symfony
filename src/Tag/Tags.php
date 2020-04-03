<?php

namespace App\Tag;

use App\Post\Event\PostPublishedEvent;
use Predis\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function ints;
use function preg_match_all;

final class Tags implements EventSubscriberInterface
{
    public const TAGS_REGEXP = '/#([^ ]+)/';
    private const TAGS_BY_EMERGENCE_REDIS_KEY = 'tag:emergence';
    private const TAGS_BY_POPULARITY_REDIS_KEY = 'tag:popularity';

    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostPublishedEvent::class => 'onPostPublished',
        ];
    }

    public function onPostPublished(PostPublishedEvent $event): void
    {
        if (!preg_match_all(self::TAGS_REGEXP, $event->getMessage(), $matches)) {
            return;
        }

        $tags = $matches[1];

        foreach ($tags as $tag) {
            $tagKey = $this->tagKey($tag);
            $this->redis->zadd($tagKey, [$event->getId() => $event->getPublished()]);

            if (null === $this->redis->zrank(self::TAGS_BY_EMERGENCE_REDIS_KEY, $tag)) {
                $this->redis->zadd(self::TAGS_BY_EMERGENCE_REDIS_KEY, [$tag => $event->getPublished()]);
            }

            $this->redis->zadd(self::TAGS_BY_POPULARITY_REDIS_KEY, [$tag => $this->redis->zcard($tagKey)]);
        }
    }

    public function tagCount(): int
    {
        return $this->redis->zcard(self::TAGS_BY_EMERGENCE_REDIS_KEY);
    }

    public function tagEmergence(string $tag): ?int
    {
        return $this->redis->zscore(self::TAGS_BY_EMERGENCE_REDIS_KEY, $tag);
    }

    public function postCount(string $tag): int
    {
        $key = $this->tagKey($tag);

        return $this->redis->zcard($key);
    }

    /**
     * @param string $tag
     * @param int    $start
     * @param int    $length
     *
     * @return int[]
     */
    public function listPostIds(string $tag, int $start = 0, int $length = 10): array
    {
        $key = $this->tagKey($tag);

        return ints($this->redis->zrevrange($key, $start, $start + $length - 1));
    }

    /**
     * @param int $start
     * @param int $length
     *
     * @return array<string,int>
     */
    public function trendingTags(int $start = 0, int $length = 10): array
    {
        return $this->redis->zrevrange(
            self::TAGS_BY_POPULARITY_REDIS_KEY,
            $start,
            $start + $length - 1,
            ['withscores' => true]
        );
    }

    private function tagKey(string $tag): string
    {
        return 'tag:' . $tag;
    }
}

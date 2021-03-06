<?php

namespace App\Post;

use App\Post\Event\PostPublishedEvent;
use App\Redis\ObjectDictionary;
use Generator;
use Predis\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class PostStorage
{
    private const REDIS_ID_KEY = 'post:next_id';

    private ObjectDictionary $objectDictionary;

    private ClientInterface $redis;

    private EventDispatcherInterface $events;

    public function __construct(
        ObjectDictionary $objectDictionary,
        ClientInterface $redis,
        EventDispatcherInterface $events
    ) {
        $this->objectDictionary = $objectDictionary;
        $this->redis = $redis;
        $this->events = $events;
    }

    public function publish(int $author, string $message, int $time = null): Post
    {
        $time ??= time();

        $id = $this->redis->incr(self::REDIS_ID_KEY);

        $post = new Post($id, $author, $message, $time);

        $key = $this->key($id);
        $dictionary = $this->objectDictionary->dictionary($post);

        $this->redis->hmset($key, $dictionary);

        $this->events->dispatch(PostPublishedEvent::fromPost($post));

        return $post;
    }

    public function get(int $id): Post
    {
        $key = $this->key($id);
        $dictionary = $this->redis->hgetall($key);

        return $this->objectDictionary->object(Post::class, $dictionary);
    }

    /**
     * @param iterable|int[] $ids
     *
     * @return Generator|Post[]
     */
    public function list(iterable $ids): Generator
    {
        foreach ($ids as $id) {
            yield $this->get($id);
        }
    }

    private function key(int $id): string
    {
        return 'post:' . $id;
    }
}

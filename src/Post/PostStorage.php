<?php

namespace App\Post;

use App\Post\Event\PostPublishedEvent;
use App\Redis\Ids;
use App\Redis\Objects;
use Generator;
use Psr\EventDispatcher\EventDispatcherInterface;

final class PostStorage
{
    /**
     * @var Ids
     */
    private Ids $ids;

    /**
     * @var Objects
     */
    private Objects $objects;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $events;

    public function __construct(
        Ids $ids,
        Objects $objects,
        EventDispatcherInterface $events
    ) {
        $this->ids = $ids;
        $this->objects = $objects;
        $this->events = $events;
    }

    public function publish(int $author, string $message): Post
    {
        $id = $this->ids->id(Post::class);

        $post = new Post($id, $author, $message, time());

        $this->objects->add((string)$id, $post);
        $this->events->dispatch(PostPublishedEvent::fromPost($post));

        return $post;
    }

    public function get(int $id): Post
    {
        return $this->objects->get(Post::class, $id);
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
}

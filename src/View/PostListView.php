<?php

namespace App\View;

use App\User\UserStorage;
use ArrayIterator;
use Hashids\HashidsInterface;
use IteratorAggregate;
use Traversable;

final class PostListView implements IteratorAggregate
{
    private array $list;

    /**
     * @param PostView[] $list
     */
    private function __construct(array $list)
    {
        $this->list = $list;
    }

    public static function new(iterable $posts, UserStorage $users, HashidsInterface $hashids)
    {
        $list = [];
        foreach ($posts as $post) {
            $list[] = PostView::new($post, $users, $hashids);
        }

        return new self($list);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->list);
    }
}

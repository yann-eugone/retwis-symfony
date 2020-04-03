<?php

namespace App\View;

use App\Like\Like;
use App\Post\Post;
use App\Post\PostStorage;
use App\User\UserStorage;
use DateTimeImmutable;
use Generator;
use Hashids\HashidsInterface;

final class ViewFactory
{
    private PostStorage $posts;

    private UserStorage $users;

    private HashidsInterface $hashids;

    private Like $likes;

    public function __construct(PostStorage $posts, UserStorage $users, HashidsInterface $hashids, Like $likes)
    {
        $this->posts = $posts;
        $this->users = $users;
        $this->hashids = $hashids;
        $this->likes = $likes;
    }

    public function post(Post $post): PostView
    {
        return new PostView(
            $post->getId(),
            $this->hashids->encode($post->getId()),
            $this->users->get($post->getAuthor()),
            $post->getMessage(),
            (new DateTimeImmutable())->setTimestamp($post->getPublished()),
            $this->likes->postCount($post->getId())
        );
    }

    /**
     * @param iterable&Post[] $posts
     *
     * @return Generator&PostView[]
     */
    public function posts(iterable $posts): Generator
    {
        foreach ($posts as $post) {
            yield $this->post($post);
        }
    }
}

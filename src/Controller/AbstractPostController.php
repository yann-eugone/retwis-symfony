<?php

namespace App\Controller;

use App\Post\Post;
use App\Post\PostStorage;
use App\Redis\NotFoundException;
use Hashids\HashidsInterface;

abstract class AbstractPostController extends Controller
{
    protected PostStorage $posts;

    protected HashidsInterface $hashids;

    public function __construct(PostStorage $posts, HashidsInterface $hashids)
    {
        $this->posts = $posts;
        $this->hashids = $hashids;
    }

    protected function getPostByHashIdOr404(string $hash): Post
    {
        $id = $this->hashids->decode($hash)[0] ?? null;
        if ($id === null) {
            throw $this->createNotFoundException();
        }

        try {
            return $this->posts->get((int)$id);
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }
    }
}

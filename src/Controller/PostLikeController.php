<?php

namespace App\Controller;

use App\Like\Like;
use App\Like\Voter\LikeVoter;
use App\Post\Post;
use App\Post\PostStorage;
use App\Redis\NotFoundException;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PostLikeController extends Controller
{
    private PostStorage $posts;

    private HashidsInterface $hashids;

    private Like $like;

    public function __construct(PostStorage $posts, HashidsInterface $hashids, Like $like)
    {
        $this->posts = $posts;
        $this->hashids = $hashids;
        $this->like = $like;
    }

    /**
     * @Route("/post/{id}/like", name="post_like", methods=Request::METHOD_GET)
     */
    public function like(string $id): Response
    {
        $post = $this->getPostByHashIdOr404($id);
        $liker = $this->getAuthenticatedUserOr403();
        $this->denyAccessUnlessGranted(LikeVoter::LIKE, $post);

        $this->like->like($post->getId(), $liker->getId());

        return $this->redirectToRoute('post_show', ['id' => $id]);
    }

    /**
     * @Route("/post/{id}/unlike", name="post_unlike", methods=Request::METHOD_GET)
     */
    public function unlike(string $id): Response
    {
        $post = $this->getPostByHashIdOr404($id);
        $liker = $this->getAuthenticatedUserOr403();
        $this->denyAccessUnlessGranted(LikeVoter::UNLIKE, $post);

        $this->like->unlike($post->getId(), $liker->getId());

        return $this->redirectToRoute('post_show', ['id' => $id]);
    }

    private function getPostByHashIdOr404(string $hash): Post
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

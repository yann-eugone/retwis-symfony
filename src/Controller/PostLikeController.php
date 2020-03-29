<?php

namespace App\Controller;

use App\Like\Like;
use App\Like\Voter\LikeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PostLikeController extends AbstractPostController
{
    /**
     * @Route("/post/{id}/like", name="post_like", methods=Request::METHOD_GET)
     */
    public function like(string $id, Like $like): Response
    {
        $post = $this->getPostByHashIdOr404($id);
        $liker = $this->getAuthenticatedUserOr403();
        $this->denyAccessUnlessGranted(LikeVoter::LIKE, $post);

        $like->like($post->getId(), $liker->getId());

        return $this->redirectToRoute('post_show', ['id' => $id]);
    }

    /**
     * @Route("/post/{id}/unlike", name="post_unlike", methods=Request::METHOD_GET)
     */
    public function unlike(string $id, Like $like): Response
    {
        $post = $this->getPostByHashIdOr404($id);
        $liker = $this->getAuthenticatedUserOr403();
        $this->denyAccessUnlessGranted(LikeVoter::UNLIKE, $post);

        $like->unlike($post->getId(), $liker->getId());

        return $this->redirectToRoute('post_show', ['id' => $id]);
    }
}

<?php

namespace App\Controller;

use App\Like\Like;
use App\Like\Voter\LikeVoter;
use App\User\UserStorage;
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

        return $this->redirectToRefererOrRoute('post_show', ['id' => $id]);
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

        return $this->redirectToRefererOrRoute('post_show', ['id' => $id]);
    }

    public function userLiking(int $id, Like $like, UserStorage $users): Response
    {
        $post = $this->getPostByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $like->userCount($post->getId());
        $count = 10;
        $start = $request->query->getInt('start');

        return $this->render('user/list.html.twig', [
            'users' => $users->list($like->listUserIds($post->getId(), $start, $count)),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

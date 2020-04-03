<?php

namespace App\Controller;

use App\Like\Like;
use App\Post\PostStorage;
use App\View\ViewFactory;
use Symfony\Component\HttpFoundation\Response;

final class UserLikeController extends AbstractUserController
{
    public function postLiked(int $id, Like $like, PostStorage $posts, ViewFactory $views): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $like->postCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $ids = $like->listPostIds($user->getId(), $start, $count);

        return $this->render('post/list.html.twig', [
            'posts' => $views->posts($posts->list($ids)),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

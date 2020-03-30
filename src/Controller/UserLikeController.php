<?php

namespace App\Controller;

use App\Like\Like;
use App\Post\PostStorage;
use App\View\PostListView;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Response;

final class UserLikeController extends AbstractUserController
{
    public function postLiked(int $id, Like $like, PostStorage $posts, HashidsInterface $hashids): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $like->postCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $posts = $posts->list($like->listPostIds($user->getId(), $start, $count));

        return $this->render('post/list.html.twig', [
            'posts' => PostListView::new($posts, $this->users, $hashids, $like),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

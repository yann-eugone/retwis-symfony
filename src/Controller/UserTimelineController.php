<?php

namespace App\Controller;

use App\Like\Like;
use App\Post\PostStorage;
use App\Timeline\MainTimeline;
use App\Timeline\PersonalTimeline;
use App\View\PostListView;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Response;

final class UserTimelineController extends AbstractUserController
{
    public function mainTimeline(
        int $id,
        MainTimeline $mainTimeline,
        PostStorage $posts,
        Like $like,
        HashidsInterface $hashids
    ): Response {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $mainTimeline->count($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $posts = $posts->list($mainTimeline->ids($user->getId(), $start, $count));

        return $this->render('post/list.html.twig', [
            'posts' => PostListView::new($posts, $this->users, $hashids, $like),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    public function personalTimeline(
        int $id,
        PersonalTimeline $personalTimeline,
        PostStorage $posts,
        Like $like,
        HashidsInterface $hashids
    ): Response {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $personalTimeline->count($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $posts = $posts->list($personalTimeline->ids($user->getId(), $start, $count));

        return $this->render('post/list.html.twig', [
            'posts' => PostListView::new($posts, $this->users, $hashids, $like),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

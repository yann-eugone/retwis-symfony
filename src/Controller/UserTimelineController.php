<?php

namespace App\Controller;

use App\Post\PostStorage;
use App\Timeline\MainTimeline;
use App\Timeline\PersonalTimeline;
use App\View\ViewFactory;
use Symfony\Component\HttpFoundation\Response;

final class UserTimelineController extends AbstractUserController
{
    public function mainTimeline(
        int $id,
        MainTimeline $mainTimeline,
        PostStorage $posts,
        ViewFactory $views
    ): Response {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $mainTimeline->count($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $ids = $mainTimeline->ids($user->getId(), $start, $count);

        return $this->render('post/list.html.twig', [
            'posts' => $views->posts($posts->list($ids)),
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
        ViewFactory $views
    ): Response {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $personalTimeline->count($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $ids = $personalTimeline->ids($user->getId(), $start, $count);

        return $this->render('post/list.html.twig', [
            'posts' => $views->posts($posts->list($ids)),
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

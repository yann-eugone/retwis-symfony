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
        $posts = $posts->list($mainTimeline->ids($user->getId()));

        return $this->render('user/timeline/main.html.twig', [
            'posts' => PostListView::new($posts, $this->users, $hashids, $like),
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
        $posts = $posts->list($personalTimeline->ids($user->getId()));

        return $this->render('user/timeline/personal.html.twig', [
            'posts' => PostListView::new($posts, $this->users, $hashids, $like),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Timeline\PersonalTimeline;
use App\User\RecentlyRegistered;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractUserController
{
    /**
     * @Route("/user/{username}", name="user_profile", methods=Request::METHOD_GET,
     *     defaults={"section"="timeline"})
     * @Route("/user/{username}/followers", name="user_followers", methods=Request::METHOD_GET,
     *     defaults={"section"="followers"})
     * @Route("/user/{username}/following", name="user_following", methods=Request::METHOD_GET,
     *     defaults={"section"="following"})
     */
    public function profile(string $username, string $section, Follow $follow, PersonalTimeline $timeline): Response
    {
        if ($username === 'me') {
            $user = $this->getAuthenticatedUserOr403();
        } else {
            $user = $this->getUserByUsernameOr404($username);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'section' => $section,
            'posts' => $timeline->count($user->getId()),
            'followers' => $follow->followersCount($user->getId()),
            'following' => $follow->followingCount($user->getId()),
        ]);
    }

    public function recentlyRegistered(RecentlyRegistered $recentlyRegistered): Response
    {
        return $this->render('user/recently-registered.html.twig', [
            'users' => $recentlyRegistered->list(),
        ]);
    }
}

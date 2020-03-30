<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Follow\Voter\FollowVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserFollowController extends AbstractUserController
{
    /**
     * @Route("/user/{username}/follow", name="user_follow", methods=Request::METHOD_GET)
     */
    public function follow(string $username, Follow $follow): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserByUsernameOr404($username);

        $this->denyAccessUnlessGranted(FollowVoter::FOLLOW, $following);

        $follow->follow($follower->getId(), $following->getId());

        return $this->redirectToRefererOrRoute('user_profile', ['username' => $username]);
    }

    /**
     * @Route("/user/{username}/unfollow", name="user_unfollow", methods=Request::METHOD_GET)
     */
    public function unfollow(string $username, Follow $follow): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserByUsernameOr404($username);

        $this->denyAccessUnlessGranted(FollowVoter::UNFOLLOW, $following);

        $follow->unfollow($follower->getId(), $following->getId());

        return $this->redirectToRefererOrRoute('user_profile', ['username' => $username]);
    }

    public function followers(int $id, Follow $follow): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $follow->followersCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $users = $this->users->list(
            $follow->followers($user->getId(), $start, $start + $count - 1)
        );

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    public function following(int $id, Follow $follow): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $follow->followingCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $users = $this->users->list(
            $follow->following($user->getId(), $start, $start + $count - 1)
        );

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }
}

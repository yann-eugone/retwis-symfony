<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Follow\MostFollowedUsers;
use App\Follow\Voter\FollowVoter;
use App\User\UserStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function flip_generator;

final class UserFollowController extends AbstractUserController
{
    /**
     * @var Follow
     */
    private Follow $follow;

    public function __construct(UserStorage $users, Follow $follow)
    {
        parent::__construct($users);
        $this->follow = $follow;
    }

    /**
     * @Route("/user/{username}/follow", name="user_follow", methods=Request::METHOD_GET)
     */
    public function follow(string $username): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserByUsernameOr404($username);

        $this->denyAccessUnlessGranted(FollowVoter::FOLLOW, $following);

        $this->follow->follow($follower->getId(), $following->getId());

        return $this->redirectToRefererOrRoute('user_profile', ['username' => $username]);
    }

    /**
     * @Route("/user/{username}/unfollow", name="user_unfollow", methods=Request::METHOD_GET)
     */
    public function unfollow(string $username): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserByUsernameOr404($username);

        $this->denyAccessUnlessGranted(FollowVoter::UNFOLLOW, $following);

        $this->follow->unfollow($follower->getId(), $following->getId());

        return $this->redirectToRefererOrRoute('user_profile', ['username' => $username]);
    }

    public function followers(int $id): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $this->follow->followersCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $users = $this->users->list(
            $this->follow->followers($user->getId(), $start, $start + $count - 1)
        );

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    public function following(int $id): Response
    {
        $user = $this->getUserByIdOr404($id);

        $request = $this->getMasterRequest();
        if ($request === null) {
            return Response::create();
        }

        $total = $this->follow->followingCount($user->getId());
        $count = 10;
        $start = $request->query->getInt('start');
        $users = $this->users->list(
            $this->follow->following($user->getId(), $start, $start + $count - 1)
        );

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'total' => $total,
            'start' => $start,
            'count' => $count,
            'request' => $request,
        ]);
    }

    public function mostFollowed(MostFollowedUsers $mostFollowed): Response
    {
        // cannot array_flip because values are scores : not unique, use a generator instead
        $usersWithFollowersCount = flip_generator(
            $mostFollowed->list(0, 5)
        );

        return $this->render('user/most-followed.html.twig', [
            'users' => $this->users->list($usersWithFollowersCount),
        ]);
    }
}

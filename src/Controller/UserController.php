<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Follow\Voter\FollowVoter;
use App\Redis\NotFoundException;
use App\User\RecentlyRegistered;
use App\User\User;
use App\User\UserStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    /**
     * @Route("/user/{username}", name="user_profile", methods=Request::METHOD_GET)
     */
    public function profile(string $username, UserStorage $users, Follow $follow): Response
    {
        if ($username === 'me') {
            $user = $this->getAuthenticatedUserOr403();
        } else {
            $user = $this->getUserOr404($username, $users);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'followers' => [
                'count' => $follow->followersCount($user->getId()),
                'list' => $users->list($follow->followers($user->getId(), 0, 10)),
            ],
            'following' => [
                'count' => $follow->followingCount($user->getId()),
                'list' => $users->list($follow->following($user->getId(), 0, 10)),
            ],
        ]);
    }

    /**
     * @Route("/user/{username}/follow", name="user_follow", methods=Request::METHOD_GET)
     */
    public function follow(string $username, UserStorage $users, Follow $follow): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserOr404($username, $users);

        $this->denyAccessUnlessGranted(FollowVoter::FOLLOW, $following);

        $follow->follow($follower->getId(), $following->getId());

        return $this->redirectToRoute('user_profile', ['username' => $username]);
    }

    /**
     * @Route("/user/{username}/unfollow", name="user_unfollow", methods=Request::METHOD_GET)
     */
    public function unfollow(string $username, UserStorage $users, Follow $follow): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserOr404($username, $users);

        $this->denyAccessUnlessGranted(FollowVoter::UNFOLLOW, $following);

        $follow->unfollow($follower->getId(), $following->getId());

        return $this->redirectToRoute('user_profile', ['username' => $username]);
    }

    public function recentlyRegistered(RecentlyRegistered $recentlyRegistered, UserStorage $users): Response
    {
        return $this->render('user/recently-registered.html.twig', [
            'users' => $users->list($recentlyRegistered->ids()),
        ]);
    }

    private function getUserOr404(string $username, UserStorage $users): User
    {
        try {
            return $users->get($users->id($username));
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }
    }

    private function getAuthenticatedUserOr403(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}

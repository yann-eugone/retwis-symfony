<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Follow\Voter\FollowVoter;
use App\Post\PostStorage;
use App\Redis\NotFoundException;
use App\Timeline\MainTimeline;
use App\Timeline\PersonalTimeline;
use App\User\RecentlyRegistered;
use App\User\User;
use App\User\UserStorage;
use App\View\PostListView;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends Controller
{
    /**
     * @Route("/user/{username}", name="user_profile", methods=Request::METHOD_GET)
     */
    public function profile(string $username, UserStorage $users): Response
    {
        if ($username === 'me') {
            $user = $this->getAuthenticatedUserOr403();
        } else {
            $user = $this->getUserByUsernameOr404($username, $users);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/{username}/follow", name="user_follow", methods=Request::METHOD_GET)
     */
    public function follow(string $username, UserStorage $users, Follow $follow): Response
    {
        $follower = $this->getAuthenticatedUserOr403();
        $following = $this->getUserByUsernameOr404($username, $users);

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
        $following = $this->getUserByUsernameOr404($username, $users);

        $this->denyAccessUnlessGranted(FollowVoter::UNFOLLOW, $following);

        $follow->unfollow($follower->getId(), $following->getId());

        return $this->redirectToRoute('user_profile', ['username' => $username]);
    }

    public function followers(int $id, UserStorage $users, Follow $follow): Response
    {
        $user = $this->getUserByIdOr404($id, $users);

        return $this->render('user/followers.html.twig', [
            'count' => $follow->followersCount($user->getId()),
            'followers' => $users->list($follow->followers($user->getId(), 0, 10)),
        ]);
    }

    public function following(int $id, UserStorage $users, Follow $follow): Response
    {
        $user = $this->getUserByIdOr404($id, $users);

        return $this->render('user/following.html.twig', [
            'count' => $follow->followingCount($user->getId()),
            'following' => $users->list($follow->following($user->getId(), 0, 10)),
        ]);
    }

    public function mainTimeline(
        int $id,
        UserStorage $users,
        MainTimeline $mainTimeline,
        PostStorage $posts,
        HashidsInterface $hashids
    ): Response {
        $user = $this->getUserByIdOr404($id, $users);
        $posts = $posts->list($mainTimeline->ids($user->getId()));

        return $this->render('user/timeline/main.html.twig', [
            'posts' => PostListView::new($posts, $users, $hashids),
        ]);
    }

    public function personalTimeline(
        int $id,
        UserStorage $users,
        PersonalTimeline $personalTimeline,
        PostStorage $posts,
        HashidsInterface $hashids
    ): Response {
        $user = $this->getUserByIdOr404($id, $users);
        $posts = $posts->list($personalTimeline->ids($user->getId()));

        return $this->render('user/timeline/personal.html.twig', [
            'posts' => PostListView::new($posts, $users, $hashids),
        ]);
    }

    public function recentlyRegistered(RecentlyRegistered $recentlyRegistered): Response
    {
        return $this->render('user/recently-registered.html.twig', [
            'users' => $recentlyRegistered->list(),
        ]);
    }

    private function getUserByUsernameOr404(string $username, UserStorage $users): User
    {
        return $this->getUserByIdOr404($users->id($username), $users);
    }

    private function getUserByIdOr404(int $id, UserStorage $users): User
    {
        try {
            return $users->get($id);
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }
    }
}

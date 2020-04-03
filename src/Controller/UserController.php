<?php

namespace App\Controller;

use App\Follow\Follow;
use App\Like\Like;
use App\Timeline\PersonalTimeline;
use App\User\Form\FillProfileForm;
use App\User\Form\FillProfileFormModel;
use App\User\RecentlyRegisteredUsers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function flip_generator;

final class UserController extends AbstractUserController
{
    /**
     * @Route("/user/{username}", name="user_profile", methods=Request::METHOD_GET,
     *     defaults={"section"="timeline"})
     * @Route("/user/{username}/followers", name="user_followers", methods=Request::METHOD_GET,
     *     defaults={"section"="followers"})
     * @Route("/user/{username}/following", name="user_following", methods=Request::METHOD_GET,
     *     defaults={"section"="following"})
     * @Route("/user/{username}/likes", name="user_likes", methods=Request::METHOD_GET,
     *     defaults={"section"="likes"})
     */
    public function profile(
        string $username,
        string $section,
        Follow $follow,
        PersonalTimeline $timeline,
        Like $like
    ): Response {
        $user = $this->getUserByUsernameOr404($username);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'section' => $section,
            'posts' => $timeline->count($user->getId()),
            'followers' => $follow->followersCount($user->getId()),
            'following' => $follow->followingCount($user->getId()),
            'likes' => $like->postCount($user->getId()),
        ]);
    }

    /**
     * @Route("/settings/profile", name="user_fill_profile", methods={Request::METHOD_GET, Request::METHOD_POST})
     */
    public function fillProfile(Request $request): Response
    {
        $user = $this->getAuthenticatedUserOr403();

        $profile = FillProfileFormModel::fromUser($user);
        $form = $this->createForm(FillProfileForm::class, $profile);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('user/profile-fill.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }

        $user->fillProfile($profile->name, $profile->bio, $profile->location, $profile->website);
        $this->users->update($user);

        return $this->redirectToRoute('user_profile', ['username' => $user->getUsername()]);
    }

    public function recentlyRegistered(RecentlyRegisteredUsers $recentlyRegistered): Response
    {
        // cannot array_flip because values are scores : not unique, use a generator instead
        $usersWithTime = flip_generator(
            $recentlyRegistered->list(0, 5)
        );

        return $this->render('user/recently-registered.html.twig', [
            'users' => $this->users->list($usersWithTime),
        ]);
    }
}

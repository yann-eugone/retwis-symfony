<?php

namespace App\Controller;

use App\User\RecentlyRegistered;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractUserController
{
    /**
     * @Route("/user/{username}", name="user_profile", methods=Request::METHOD_GET)
     */
    public function profile(string $username): Response
    {
        if ($username === 'me') {
            $user = $this->getAuthenticatedUserOr403();
        } else {
            $user = $this->getUserByUsernameOr404($username);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    public function recentlyRegistered(RecentlyRegistered $recentlyRegistered): Response
    {
        return $this->render('user/recently-registered.html.twig', [
            'users' => $recentlyRegistered->list(),
        ]);
    }
}

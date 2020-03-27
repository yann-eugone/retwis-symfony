<?php

namespace App\Controller;

use App\Redis\NotFoundException;
use App\User\RecentlyRegisteredUsers;
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
    public function profile(string $username, UserStorage $users): Response
    {
        if ($username === 'me') {
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            try {
                $user = $users->get($users->id($username));
            } catch (NotFoundException $exception) {
                throw $this->createNotFoundException('Not Found', $exception);
            }
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    public function recentlyRegistered(RecentlyRegisteredUsers $list): Response
    {
        return $this->render('user/recently-registered.html.twig', ['users' => $list->get()]);
    }
}

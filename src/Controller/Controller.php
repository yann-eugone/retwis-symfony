<?php

namespace App\Controller;

use App\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use function parse_url;
use const PHP_URL_HOST;

abstract class Controller extends AbstractController
{
    protected function getAuthenticatedUserOr403(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    protected function redirectToRefererOrRoute(string $route = null, array $parameters = []): RedirectResponse
    {
        $referer = $this->getValidReferer();
        if ($referer !== null) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute($route ?: 'index', $parameters);
    }

    protected function getValidReferer(): ?string
    {
        $request = $this->getMasterRequest();
        if ($request === null) {
            return null;
        }
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            return null;
        }
        if ($request->getHost() !== parse_url($referer, PHP_URL_HOST)) {
            return null;
        }

        return $referer;
    }

    protected function getMasterRequest(): ?Request
    {
        return $this->get('request_stack')->getMasterRequest();
    }
}

<?php

namespace App\Controller;

use App\Security\Authenticator;
use App\User\Form\RegisterForm;
use App\User\Form\RegisterFormModel;
use App\User\UserStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class StaticController extends Controller
{
    /**
     * @Route("/", name="index", methods=Request::METHOD_GET)
     */
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->render('index-authenticated.html.twig');
        }

        return $this->render('index-anonymous.html.twig');
    }

    /**
     * @Route("/register", name="register", methods={Request::METHOD_GET, Request::METHOD_POST})
     */
    public function register(
        Request $request,
        UserStorage $users,
        GuardAuthenticatorHandler $guard,
        Authenticator $authenticator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $register = new RegisterFormModel();
        $form = $this->createForm(RegisterForm::class, $register);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('register.html.twig', ['form' => $form->createView()]);
        }

        $user = $users->register($register->name, $register->username, $register->password);

        $response = $guard->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        if (!$response) {
            $response = $this->redirectToRoute('index');
        }

        return $response;
    }

    /**
     * @Route("/login", name="login", methods={Request::METHOD_GET, Request::METHOD_POST})
     */
    public function login(AuthenticationUtils $auth): Response
    {
        return $this->render('login.html.twig', [
            'last_username' => $auth->getLastUsername(),
            'error' => $auth->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="logout", methods=Request::METHOD_GET)
     */
    public function logout(LoggerInterface $logger): Response
    {
        $logger->critical(__METHOD__ . ' method should not be accessed.');

        return $this->redirectToRoute('index');
    }
}

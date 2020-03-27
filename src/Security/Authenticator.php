<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use function array_flip;
use function array_intersect_key;

final class Authenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private UrlGeneratorInterface $router;

    private CsrfTokenManagerInterface $csrf;

    private UserPasswordEncoderInterface $password;

    public function __construct(
        UrlGeneratorInterface $router,
        CsrfTokenManagerInterface $csrf,
        UserPasswordEncoderInterface $password
    ) {
        $this->router = $router;
        $this->csrf = $csrf;
        $this->password = $password;
    }

    public function supports(Request $request): bool
    {
        return 'login' === $request->attributes->get('_route')
            && $request->isMethod(Request::METHOD_POST);
    }

    public function getCredentials(Request $request): array
    {
        $credentials = array_intersect_key(
            $request->request->all(),
            array_flip(['username', 'password', 'csrf_token'])
        );
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrf->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->password->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('index'));
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('login');
    }
}

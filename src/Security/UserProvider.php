<?php

namespace App\Security;

use App\User\User;
use App\User\UserStorage;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Throwable;
use function sprintf;

final class UserProvider implements UserProviderInterface
{
    /**
     * @var UserStorage
     */
    private UserStorage $users;

    public function __construct(UserStorage $users)
    {
        $this->users = $users;
    }

    public function loadUserByUsername(string $username): User
    {
        try {
            return $this->users->get(
                $this->users->id($username)
            );
        } catch (Throwable $exception) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username), 0, $exception);
        }
    }

    public function refreshUser(UserInterface $user): User
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}

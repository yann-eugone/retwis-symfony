<?php

namespace App\Follow\Voter;

use App\Exception\UnreachableCodeException;
use App\Follow\Follow;
use App\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class FollowVoter extends Voter
{
    public const FOLLOW = 'user.follow';
    public const UNFOLLOW = 'user.unfollow';

    private Follow $follow;

    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::FOLLOW, self::UNFOLLOW], true)
            && $subject instanceof User;
    }

    /**
     * @param string         $attribute
     * @param User           $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            throw UnreachableCodeException::classMethodPart(__METHOD__, __LINE__);
        }

        if ($user->getId() === $subject->getId()) {
            return false;
        }

        $isFollowing = $this->follow->isFollowing($user->getId(), $subject->getId());

        switch ($attribute) {
            case self::FOLLOW:
                return !$isFollowing;
            case self::UNFOLLOW:
                return $isFollowing;
        }

        throw UnreachableCodeException::classMethodPart(__METHOD__, __LINE__);
    }
}

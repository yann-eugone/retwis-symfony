<?php

namespace App\Like\Voter;

use App\Exception\UnreachableCodeException;
use App\Like\Like;
use App\Post\Post;
use App\User\User;
use App\View\PostView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LikeVoter extends Voter
{
    public const LIKE = 'post.like';
    public const UNLIKE = 'post.unlike';

    private Like $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::LIKE, self::UNLIKE], true)
            && ($subject instanceof Post || $subject instanceof PostView);
    }

    /**
     * @param string         $attribute
     * @param Post|PostView  $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $isLiking = $this->like->isLiking($subject->getId(), $user->getId());

        switch ($attribute) {
            case self::LIKE:
                return !$isLiking;
            case self::UNLIKE:
                return $isLiking;
        }

        throw UnreachableCodeException::classMethodPart(__METHOD__, __LINE__);
    }
}

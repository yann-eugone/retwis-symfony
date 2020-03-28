<?php

namespace App\Post\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PublishVoter extends Voter
{
    public const PUBLISH = 'post.publish';

    private AccessDecisionManagerInterface $access;

    public function __construct(AccessDecisionManagerInterface $access)
    {
        $this->access = $access;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::PUBLISH && $subject === null;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return $this->access->decide($token, [AuthenticatedVoter::IS_AUTHENTICATED_FULLY]);
    }
}

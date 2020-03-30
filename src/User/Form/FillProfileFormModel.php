<?php

namespace App\User\Form;

use App\User\User;
use Symfony\Component\Validator\Constraints as Assert;

final class FillProfileFormModel
{
    /**
     * @Assert\NotNull()
     * @Assert\Length(max=50)
     */
    public ?string $name = null;

    /**
     * @Assert\Length(max=160)
     */
    public ?string $bio = null;

    /**
     * @Assert\Length(max=30)
     */
    public ?string $location = null;

    /**
     * @Assert\Length(max=100)
     * @Assert\Url()
     */
    public ?string $website = null;

    public static function fromUser(User $user): self
    {
        $profile = new self();
        $profile->name = $user->getName();
        $profile->bio = $user->getBio();
        $profile->location = $user->getLocation();
        $profile->website = $user->getWebsite();

        return $profile;
    }
}

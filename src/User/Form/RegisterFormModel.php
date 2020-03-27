<?php

namespace App\User\Form;

use App\User\Validator\UniqueUsername;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterFormModel
{
    /**
     * @Assert\NotNull()
     * @Assert\Length(min=5, max=25)
     * @UniqueUsername()
     */
    public ?string $username = null;

    /**
     * @Assert\NotNull()
     * @Assert\Length(min=8)
     */
    public ?string $password = null;
}

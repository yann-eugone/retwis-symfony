<?php

namespace App\Post\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class PublishFormModel
{
    /**
     * @Assert\NotNull()
     * @Assert\Length(min=1, max=280)
     */
    public ?string $message = null;
}

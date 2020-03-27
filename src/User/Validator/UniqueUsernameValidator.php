<?php

namespace App\User\Validator;

use App\Redis\Identifiers;
use App\User\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use function is_string;

final class UniqueUsernameValidator extends ConstraintValidator
{
    private Identifiers $identifiers;

    public function __construct(Identifiers $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUsername) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
        }

        if ($value === null) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->identifiers->has(User::class, $value)) {
            $this->context->addViolation('This username is already used.');
        }
    }
}

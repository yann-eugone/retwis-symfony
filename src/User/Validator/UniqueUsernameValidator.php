<?php

namespace App\User\Validator;

use App\Redis\NotFoundException;
use App\User\UserStorage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use function is_string;

final class UniqueUsernameValidator extends ConstraintValidator
{
    private UserStorage $users;

    public function __construct(UserStorage $users)
    {
        $this->users = $users;
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

        $used = true;
        try {
            $this->users->id($value);
        } catch (NotFoundException $exception) {
            $used = false;
        }

        if ($used) {
            $this->context->addViolation('This username is already used.');
        }
    }
}

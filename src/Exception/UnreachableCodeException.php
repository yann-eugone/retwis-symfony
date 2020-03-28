<?php

namespace App\Exception;

use LogicException;

final class UnreachableCodeException extends LogicException
{
    public static function classMethodPart(string $method, int $line): self
    {
        return new self(
            sprintf('%s::%d should not be reached, please review your code.', $method, $line)
        );
    }
}

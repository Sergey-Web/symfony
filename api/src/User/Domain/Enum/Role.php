<?php

namespace App\User\Domain\Enum;

use InvalidArgumentException;

enum Role: string
{
    case User = 'ROLE_USER';

    case Admin = 'ROLE_ADMIN';

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException(
            'Invalid user role: ' . $value . '.'
        );
    }
}

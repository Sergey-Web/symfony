<?php

declare(strict_types=1);

namespace App\User\Domain\Enum;

use InvalidArgumentException;

enum ExternalProvider: string
{
    case Google = 'google';
    case Telegram = 'telegram';

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException(
            'Invalid auth provider: ' . $value . '.'
        );
    }
}

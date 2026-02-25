<?php

namespace App\User\Domain\Enum;

enum UserStatus: string
{
    case Active = 'active';
    case Wait = 'wait';

    public function isWaiting(): bool
    {
        return $this === self::Wait;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}

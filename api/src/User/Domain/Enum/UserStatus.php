<?php

declare(strict_types=1);

namespace App\User\Domain\Enum;

enum UserStatus: string
{
    case Active = 'active';
    case Wait = 'wait';
    case Block = 'block';
}

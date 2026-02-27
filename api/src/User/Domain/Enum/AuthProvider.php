<?php

declare(strict_types=1);

namespace App\User\Domain\Enum;

enum AuthProvider: string
{
    case Google = 'google';
}

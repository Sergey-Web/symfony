<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

use App\User\Domain\ValueObject\Password;

interface PasswordHasher
{
    public function hash(Password $password): string;

    public function verify(Password $password, string $hash): bool;
}

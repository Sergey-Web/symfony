<?php

declare(strict_types=1);

namespace App\User\Domain\Service;

interface PasswordHasher
{
    public function hash(string $password): string;

    public function verify(string $password, string $hash): bool;
}

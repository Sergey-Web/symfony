<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Domain\Service\PasswordHasher;

final class NativePasswordHasher implements PasswordHasher
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

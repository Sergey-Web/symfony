<?php

declare(strict_types=1);

namespace App\User\Application\Command\ResetPassword;

final readonly class Command
{
    public function __construct(
        private(set) string $userId,
        private(set) string $resetToken,
        private(set) string $password,
    ) {}
}

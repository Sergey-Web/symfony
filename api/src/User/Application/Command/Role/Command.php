<?php

declare(strict_types=1);

namespace App\User\Application\Command\Role;

final readonly class Command
{
    public function __construct(
        private(set) string $userId,
        private(set) string $role,
    ) {}
}

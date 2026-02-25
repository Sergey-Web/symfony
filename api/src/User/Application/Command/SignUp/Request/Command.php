<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Request;

final readonly class Command
{
    public function __construct(
        private(set) string $email,
        private(set) string $password
    ) {}
}

<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUpByEmail;

final readonly class Command
{
    public function __construct(
        private(set) string $firstName,
        private(set) string $lastName,
        private(set) string $email,
        private(set) string $password
    ) {}
}

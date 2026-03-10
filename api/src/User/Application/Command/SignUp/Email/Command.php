<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Email;

final readonly class Command
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password
    ) {}
}

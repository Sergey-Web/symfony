<?php

declare(strict_types=1);

namespace App\User\Application\Command\RequestPasswordReset;

final readonly class Command
{
    public function __construct(
        public string $email,
    ) {}
}

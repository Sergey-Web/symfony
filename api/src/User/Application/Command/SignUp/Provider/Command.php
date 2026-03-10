<?php

declare(strict_types=1);

namespace App\User\Application\Command\SignUp\Provider;

final readonly class Command
{
    public function __construct(
        public string $provider,
        public string $externalId,
        public string $firstName,
        public string $lastName,
    ) {}
}

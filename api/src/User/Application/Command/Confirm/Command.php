<?php

declare(strict_types=1);

namespace App\User\Application\Command\Confirm;

final readonly class Command
{
    public function __construct(
        private(set) string $confirmToken,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\AuthProvider;

class Network
{
    public function __construct(
        private(set) User $user,
        private(set) AuthProvider $provider,
        private(set) string $externalId,
    ) {
    }
}

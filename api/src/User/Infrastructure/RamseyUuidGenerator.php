<?php

declare(strict_types=1);

namespace App\User\Infrastructure;

use App\User\Domain\Service\UuidGenerator;
use Ramsey\Uuid\Uuid;

final class RamseyUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return Uuid::uuid7()->toString();
    }
}

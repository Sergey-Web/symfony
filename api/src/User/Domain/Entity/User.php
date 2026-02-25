<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;

final readonly class User
{
    public function __construct(
        private(set) UserId $id,
        private(set) Email $email,
        private(set) string $hash,
        private(set) DateTimeImmutable $date,
    ) {
    }
}

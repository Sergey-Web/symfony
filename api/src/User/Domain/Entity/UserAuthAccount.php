<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\ValueObject\Id;
use DateTimeImmutable;
use DomainException;

final class UserAuthAccount
{
    private(set) Id $id;

    public function __construct(
        private(set) ?User $user,
        private(set) ExternalProvider $provider,
        private(set) string $externalId,
        private(set) DateTimeImmutable $createdAt,
    ) {
        $this->id = Id::next();
    }

    public function assignToUser(User $user): void
    {
        if ($this->user !== null && $this->user !== $user) {
            throw new DomainException('Auth account already assigned.');
        }

        $this->user = $user;
    }

    public function isSame(ExternalProvider $provider, string $externalId): bool
    {
        return $this->provider === $provider && $this->externalId === $externalId;
    }
}

<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\AuthProvider;
use App\User\Domain\ValueObject\Id;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

final class UserAuthAccount
{
    private ?User $user = null;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        private(set) Id $id,

        private(set) ?Id $userId,
        private(set) AuthProvider  $provider,
        private(set) string $externalId,
        private(set) DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        AuthProvider $provider,
        string $externalId,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: Id::next(),
            userId: null,
            provider: $provider,
            externalId: $externalId,
            createdAt: $createdAt,
        );
    }

    public function assignToUser(User $user): void
    {
        if ($this->user !== null && $this->user !== $user) {
            throw new DomainException('Auth account already assigned.');
        }

        $this->user = $user;
    }
}

<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\ValueObject\Id;
use DateTimeImmutable;
use Doctrine\DBAL\Schema\UniqueConstraint;
use Doctrine\DBAL\Types\Types;
use DomainException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_auth_accounts')]
#[ORM\UniqueConstraint(name: 'user_provider_unique', columns: ['user_id', 'provider'])]
final class UserAuthAccount
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private(set) Id $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userAuthAccounts')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Column(name: 'provider', nullable: false, enumType: ExternalProvider::class)]
        private(set) ExternalProvider $provider,
        #[ORM\Column(name: 'external_id', type: Types::STRING, unique: true, nullable: false)]
        private(set) string $externalId,
        #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false)]
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

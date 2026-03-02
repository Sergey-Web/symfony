<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\AuthProvider;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Id;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use DomainException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

final class User
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        private(set) Id $id,

        #[ORM\Column(type: 'email', unique: true, nullable: true)]
        private(set) ?Email $email,

        #[ORM\Embedded(class: Name::class)]
        private(set) Name $name,

        #[ORM\Column(name: 'password_hash', type: 'string', nullable: true)]
        private(set) ?string $passwordHash,

        #[ORM\Column(name: 'created_at', type: 'created_at', nullable: false)]
        private(set) DateTimeImmutable $createdAt,

        #[ORM\Embedded(class: ConfirmToken::class)]
        private(set) readonly ?ConfirmToken $confirmToken,

        #[ORM\Column(name: 'status', type: 'status', nullable: false)]
        private(set) UserStatus $status,

        #[ORM\OneToMany(targetEntity: UserAuthAccount::class, mappedBy: 'user')]
        private(set) ArrayCollection $userAuthAccounts = new ArrayCollection(),
    ) {}

    public static function signUpByEmail(
        Id $id,
        Email $email,
        Name $name,
        ConfirmToken $confirmToken,
        string $hash,
        DateTimeImmutable $createdAt,
    ): self
    {
        return new self(
            id: $id,
            email: $email,
            name: $name,
            passwordHash: $hash,
            createdAt: $createdAt,
            confirmToken: $confirmToken,
            status: UserStatus::Wait
        );
    }

    public static function signUpByAuth(
        Id $id,
        AuthProvider $provider,
        string $externalId,
        Name $name,
        DateTimeImmutable $createdAt
    ): self
    {
        $userAuthAccount = UserAuthAccount::create(
            provider: $provider,
            externalId: $externalId,
            createdAt: $createdAt
        );

        return new self(
            id: $id,
            email: null,
            name: $name,
            passwordHash: null,
            createdAt: $createdAt,
            confirmToken: null,
            status: UserStatus::Active,
            userAuthAccounts: new ArrayCollection([$userAuthAccount])
        );
    }

    public function confirmSignUp(ConfirmToken $confirmToken): void
    {
        if ($this->status !== UserStatus::Wait) {
            throw new DomainException('User already confirmed.');
        }

        if ($this->confirmToken->value !== $confirmToken->value) {
            throw new DomainException('Invalid confirm token.');
        }

        $this->status = UserStatus::Active;
    }

    public function attachAuthAccount(
        AuthProvider $provider,
        string $externalId,
        DateTimeImmutable $createdAt
    ): void {
        foreach ($this->userAuthAccounts as $account) {
            if ($account->isSame($provider, $externalId)) {
                throw new DomainException('OAuth account already attached.');
            }
        }

        $account = UserAuthAccount::create($provider, $externalId, $createdAt);
        $account->assignToUser($this);
        $this->userAuthAccounts->add($account);
    }
}

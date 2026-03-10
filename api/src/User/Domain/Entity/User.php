<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\ResetToken;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use DomainException;
use Doctrine\ORM\Mapping as ORM;

final class User
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        private(set) Id $id,

        #[ORM\Embedded(class: Email::class)]
        private(set) ?Email $email,

        #[ORM\Embedded(class: Name::class)]
        private(set) Name $name,

        #[ORM\Embedded(class: Password::class)]
        private(set) ?Password $password,

        #[ORM\Column(name: 'created_at', type: 'created_at', nullable: false)]
        private(set) DateTimeImmutable $createdAt,

        #[ORM\Column(name: 'status', type: 'status', nullable: false)]
        private(set) UserStatus $status,

        #[ORM\OneToMany(targetEntity: UserAuthAccount::class, mappedBy: 'user')]
        private ArrayCollection $userAuthAccounts = new ArrayCollection(),

        #[ORM\Embedded(class: ConfirmToken::class)]
        private(set) ?ConfirmToken $confirmToken = null,

        #[ORM\Embedded(class: ResetToken::class)]
        private(set) ?ResetToken $resetToken = null,
    ) {}

    public static function signUpWithEmail(
        Id $id,
        Email $email,
        Name $name,
        ConfirmToken $confirmToken,
        Password $password,
        DateTimeImmutable $createdAt,
    ): self
    {
        return new self(
            id: $id,
            email: $email,
            name: $name,
            password: $password,
            createdAt: $createdAt,
            status: UserStatus::Wait,
            confirmToken: $confirmToken
        );
    }

    public static function signUpWithExternalProvider(
        Id $id,
        ExternalProvider $provider,
        string $externalId,
        Name $name,
        DateTimeImmutable $createdAt
    ): self
    {
        $user = new self(
            id: $id,
            email: null,
            name: $name,
            password: null,
            createdAt: $createdAt,
            status: UserStatus::Active,
        );

        $user->attachExternalProvider($provider, $externalId, $createdAt);

        return $user;
    }

    public function confirmSignUp(ConfirmToken $confirmToken): void
    {
        if ($this->status !== UserStatus::Wait) {
            throw new DomainException('User already confirmed.');
        }

        if (!$this->hasConfirmToken($confirmToken)) {
            throw new DomainException('Invalid confirm token.');
        }

        $this->status = UserStatus::Active;
    }

    public function attachExternalProvider(
        ExternalProvider $provider,
        string $externalId,
        DateTimeImmutable $createdAt
    ): void
    {
        /** @var UserAuthAccount $account */
        foreach ($this->userAuthAccounts as $account) {
            if ($account->isSame($provider, $externalId)) {
                throw new DomainException('Auth account already attached.');
            }
        }

        $account = new UserAuthAccount($this, $provider, $externalId, $createdAt);
        $account->assignToUser($this);
        $this->userAuthAccounts->add($account);
    }

    public function hasConfirmToken(ConfirmToken $confirmToken): bool
    {
        return $this->confirmToken->value === $confirmToken->value;
    }

    public function authAccounts(): array
    {
        return $this->userAuthAccounts->toArray();
    }

    public function requestPasswordReset(ResetToken $resetToken): void
    {
        if (!$this->email) {
            throw new DomainException('Email not set.');
        }

        $this->resetToken = $resetToken;
    }

    public function resetPassword(Password $password): void
    {
        $this->password = $password;
    }
}

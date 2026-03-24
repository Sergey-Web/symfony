<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\UserRole;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\ResetToken;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use DomainException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
final class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private(set) string $id;

    private function __construct(
        Id $id,

        #[ORM\Embedded(class: Email::class, columnPrefix: false)]
        private(set) ?Email $email,

        #[ORM\Embedded(class: Name::class, columnPrefix: false)]
        private(set) Name $name,

        #[ORM\Embedded(class: Password::class, columnPrefix: false)]
        private(set) ?Password $password,

        #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false)]
        private(set) DateTimeImmutable $createdAt,

        #[ORM\Column(name: 'status', length: 16, nullable: false, enumType: UserStatus::class)]
        private(set) UserStatus $status,

        #[ORM\Column(name: 'role', length: 16, nullable: false, enumType: UserRole::class)]
        private(set) UserRole $role,

        #[ORM\OneToMany(targetEntity: UserAuthAccount::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
        private ArrayCollection $userAuthAccounts = new ArrayCollection(),

        #[ORM\Embedded(class: ConfirmToken::class, columnPrefix: false)]
        private(set) ?ConfirmToken $confirmToken = null,

        #[ORM\Embedded(class: ResetToken::class, columnPrefix: false)]
        private(set) ?ResetToken $resetToken = null,
    ) {
        $this->id = $id->value;
    }

    public static function signUpWithEmail(
        Id $id,
        Email $email,
        Name $name,
        ConfirmToken $confirmToken,
        Password $password,
        DateTimeImmutable $createdAt,
        UserRole $role
    ): self
    {
        return new self(
            id: $id,
            email: $email,
            name: $name,
            password: $password,
            createdAt: $createdAt,
            status: UserStatus::Wait,
            role: $role,
            confirmToken: $confirmToken
        );
    }

    public static function signUpWithExternalProvider(
        Id $id,
        ExternalProvider $provider,
        string $externalId,
        Name $name,
        DateTimeImmutable $createdAt,
        UserRole $role
    ): self
    {
        $user = new self(
            id: $id,
            email: null,
            name: $name,
            password: null,
            createdAt: $createdAt,
            status: UserStatus::Active,
            role: $role

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
        if ($this->status !== UserStatus::Active) {
            throw new DomainException('User is not active.');
        }

        if (!$this->email) {
            throw new DomainException('Email not set.');
        }

        $this->resetToken = $resetToken;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resetPassword(Password $password, DateTimeImmutable $date): void
    {
        if ($this->resetToken->isExpired($date)) {
            throw new DomainException('Reset token is expired.');
        }

        if ($this->resetToken->isTooEarly($date)) {
            throw new DomainException('Reset token is too early.');
        }

        $this->password = $password;
    }

    public function block(): void
    {
        if ($this->status === UserStatus::Block) {
            throw new DomainException('User is already blocked.');
        }

        $this->status = UserStatus::Block;
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->status !== UserStatus::Active) {
            throw new DomainException('User is not active.');
        }

        $this->email = $newEmail;
    }

    public function changeRole(UserRole $role): void
    {
        if ($this->role === $role) {
            throw new DomainException('This role is already assigned.');
        }

        $this->role = $role;
    }

    public function getPassword(): ?string
    {
        return $this->password->value;
    }
}

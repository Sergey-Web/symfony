<?php

declare(strict_types=1);

namespace App\Tests\Builder\User;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\ExternalProvider;
use App\User\Domain\Enum\UserRole;
use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\ResetToken;
use BadMethodCallException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

class UserBuilder
{
    private ?Email $email = null;

    private ?Password $password = null;

    private ?ConfirmToken $confirmToken = null;
    private ?ExternalProvider $externalProvider = null;
    private ?string $externalProviderId = null;

    private bool $confirmed = false;

    public function __construct(
        public ?Id $id = null,
        public ?Name $name = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?UserStatus $status = null,
        public ?UserRole $role = null,
        public ArrayCollection $userAuthAccounts = new ArrayCollection(),
        public ?ResetToken $resetToken = null,
    ) {
        $this->id = $id ?? Id::next();
        $this->status = $status ?? UserStatus::Wait;
        $this->role = $role ?? UserRole::User;
        $this->name = $name ?? new Name('John', 'Doe');
        $this->createdAt = $createdAt ?? new DateTimeImmutable();

    }

    public function viaSignUpEmail(
        ?Email $email = null,
        ?Password $password = null,
        ?ConfirmToken $confirmToken = null,
    ): self
    {
        $this->email = $email ?? new Email('test@example.com');;
        $this->password = $password ?? new Password('secret123');
        $this->confirmToken = $confirmToken ?? ConfirmToken::generate();

        return $this;
    }

    public function confirmed(): self
    {
        $this->confirmed = true;

        return $this;
    }

    public function viaSignUpExternalProvider(
        ?ExternalProvider $externalProvider = null,
        ?string $externalProviderId = null,
    ): self
    {
        $this->externalProvider = $externalProvider ?? ExternalProvider::Google;
        $this->externalProviderId = $externalProviderId ?? 'google-123456';

        return $this;
    }

    public function build(): User
    {
        $user = null;

        if ($this->email) {
            $user = User::signUpWithEmail(
                id: $this->id,
                email: $this->email,
                name: $this->name,
                confirmToken: $this->confirmToken,
                password: $this->password,
                createdAt: $this->createdAt,
                role: $this->role,
            );

            if ($this->confirmed) {
                $user->confirmSignUp($user->confirmToken);
            }
        }

        if ($this->externalProvider) {
            $user = User::signUpWithExternalProvider(
                id: $this->id,
                provider: $this->externalProvider,
                externalId: $this->externalProviderId,
                name: $this->name,
                createdAt: $this->createdAt,
                role: $this->role,
            );
        }

        if (!$user) {
            throw new BadMethodCallException('Please select a registration method via.');
        }

        return $user;
    }
}

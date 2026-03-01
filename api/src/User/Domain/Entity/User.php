<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Name;
use App\User\Domain\ValueObject\Id;
use DateTimeImmutable;
use DomainException;

final class User
{
    private function __construct(
        private(set) readonly Id $id,
        private(set) readonly Email $email,
        private(set) readonly Name $name,
        private(set) readonly string $hash,
        private(set) readonly DateTimeImmutable $createdAt,
        private(set) readonly ConfirmToken $confirmToken,
        private(set) UserStatus $status,
    ) {
    }

    public static function signUpByEmail(
        Email $email,
        Name $name,
        string $hash,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: Id::next(),
            email: $email,
            name: $name,
            hash: $hash,
            createdAt: $createdAt,
            confirmToken: ConfirmToken::generate(),
            status: UserStatus::Wait
        );
    }

//    public static function signUpByNetwork(Id $id, Name $name, DateTimeImmutable $date, string $identity): self
//    {
//        $user = new self($id, $date, $name);
//        $user->attachNetwork($network, $identity);
//        $user->status = self::STATUS_ACTIVE;
//        return $user;
//    }

    public function confirmSignUp(): void
    {
        if ($this->status !== UserStatus::Wait) {
            throw new DomainException('User already confirmed.');
        }

        $this->status = UserStatus::Active;
    }
}

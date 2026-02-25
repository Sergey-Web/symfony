<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\UserStatus;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use DomainException;

final class User
{
    public function __construct(
        private(set) readonly UserId $id,
        private(set) readonly Email $email,
        private(set) readonly string $hash,
        private(set) readonly DateTimeImmutable $date,
        private(set) readonly ConfirmToken $signUpToken,
        private(set) UserStatus $status = UserStatus::Wait,
    ) {
    }

    public function confirmSignUp(): void
    {
        if ($this->status !== UserStatus::Wait) {
            throw new DomainException('User already confirmed.');
        }

        $this->status = UserStatus::Active;
    }
}

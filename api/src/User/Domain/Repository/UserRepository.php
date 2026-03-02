<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Enum\AuthProvider;
use App\User\Domain\ValueObject\ConfirmToken;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Id;

interface UserRepository
{
    public function findByEmail(Email $email): ?User;

    public function findByUserId(Id $userId): ?User;

    public function add(User $user): void;

    public function existsByEmail(Email $email): bool;

    public function findByConfirmToken(ConfirmToken $confirmToken): ?User;

    public function existsByAuthProvider(AuthProvider $provider, string $externalId): bool;
}
